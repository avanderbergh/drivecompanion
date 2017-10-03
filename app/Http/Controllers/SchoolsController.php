<?php

namespace App\Http\Controllers;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\School;
use JavaScript;
use App\Section;
use Carbon\Carbon;
use App\Http\Requests;
use Google_Auth_Exception;
use Illuminate\Http\Request;
use PulkitJalan\Google\Client;
use Google_Service_Drive_DriveFile;
use Avanderbergh\Schoology\Facades\Schoology;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DrawMyAttention\XeroLaravel\Facades\XeroPrivate;
use XeroPHP\Models\Accounting\Address;
use XeroPHP\Models\Accounting\Contact;
use XeroPHP\Models\Accounting\Contact\ContactPerson;
use XeroPHP\Models\Accounting\Phone;
use Mail;

class SchoolsController extends Controller
{
    public function show(Request $request)
    {
        Schoology::authorize();
        $user = Schoology::apiResult('users/me');

        $email = $user->primary_email;
        $school_id = $request->session()->get('schoology')['school_nid'];

        $result = Schoology::apiResult('schools/'.$school_id);
        try {
            $school = School::findOrFail($school_id);
        } catch (ModelNotFoundException $e) {
            $school = School::create([
                'id' => $result->id,
                'name' => $result->title,
                'address' => $result->address1."\r\n".$result->address2,
                'city' => $result->city,
                'state' => $result->state,
                'postal_code' => $result->postal_code,
                'country' => $result->country,
            ]);
            $school->save();
            $school = School::find($school_id);
        }
        JavaScript::put([
            'school_id' => $school_id,
            'google_api_configured' => $school->google_api_configured,
            'credits' => $school->credits,
            'price' => config('freshbooks.cost'),
            'school' => (array) $result,
            'user' => (array) $user,
            'domain' => 'https://'.session('schoology')['domain'],
            'code' => $school->code,
        ]);
        return view("config.school")
            ->with('school', $school)
            ->with('email', $email)
            ->with('user', $user);
    }

    public function testGoogleAuth(Request $request)
    {
        $email = $request->email;
        $config = config('google');
        $school = School::findOrFail($request->school_id);
        $result = 'success';
        $client = new Client($config, $email);
        try {
            $service = $client->make('drive');
            $fileMetadata = new Google_Service_Drive_DriveFile(array(
                'name' => 'DC TEST FOLDER',
                'mimeType' => 'application/vnd.google-apps.folder'));
            $file = $service->files->create($fileMetadata, array(
                'fields' => 'id'));
            $service->files->delete($file->id);
        } catch (\Exception $e) {
            $school->google_api_configured=false;
            $result = 'fail';
        }
        if ($result=='success') {
            $school->google_api_configured=true;
        }
        $school->save();
        return $result;
    }

    /*
     * Count the number of Schoology sections for current grading periods
     */
    public function currentSections()
    {
        Schoology::authorize();
        $result = Schoology::apiResult('courses');
        $courses = array();
        while (property_exists($result->links, 'next')) {
            $courses = array_merge($courses, $result->course);
            $result = Schoology::apiResult('courses'.substr($result->links->next, strpos($result->links->next, '?')));
        }
        $courses = array_merge($courses, $result->course);
        $current_sections = 0;
        foreach ($courses as $course) {
            $result = Schoology::apiResult('courses/'.$course->id.'/sections');
            $current_sections += $result->total;
        }
        return $current_sections;
    }
    /*
     * Check how many DriveCompanion Sections are active since the start of the subscription.
     */
    public function getSections($school_id)
    {
        $school = School::find($school_id);
        return $school->sections;
    }
    /*
     * Fetch the Contact from XERO Api
     */
    public function getXeroContact($school_id)
    {
        if ($contact = array_first(XeroPrivate::load('Accounting\\Contact')
            ->where('AccountNumber', $school_id)
            ->execute())){
            return $contact;
        } else {
            return;
        }
    }

    /*
     * Save the Contact to XERO Api
     */
    public function setXeroContact($school_id, Request $request)
    {
        $xero = app()->make('XeroPrivate');

        if (!$contact = array_first(XeroPrivate::load('Accounting\\Contact')
            ->where('AccountNumber', $school_id)
            ->execute())){
            $contact = app()->make('XeroContact');
            $contact->setAccountNumber($school_id);
        }
        $contact->setContactStatus('ACTIVE');
        $contact->setName($request->organization);
        $contact->setFirstName($request->first_name);
        $contact->setLastName($request->name_last);
        $contact->setEmailAddress($request->email);
        $contact->setDefaultCurrency('USD');
        $address = new Address($xero);

        $address->setAddressType('POBOX');
        $address->setAddressLine1($request->address1);
        $address->setAddressLine2($request->address2);
        $address->setPostalCode($request->postal_code);
        $address->setCity($request->city);
        $address->setRegion($request->state);
        $address->setCountry($request->country);
        $contact->addAddress($address);

        $phone = new Phone($xero);
        $phone->setPhoneType('DEFAULT');
        $phone->setPhoneCountryCode($request->phone['country_code']);
        $phone->setPhoneAreaCode($request->phone['area_code']);
        $phone->setPhoneNumber($request->phone['number']);

        $contact->AddPhone($phone);
        $xero->save($contact);
        return 'Contact Saved';
    }
    /*
     * Add more sections credits and invoice the client.
     */
    public function buyCredits($school_id, Request $request)
    {
        Schoology::authorize();
        $user = Schoology::apiResult('users/me');
        $school = School::findOrFail($school_id);
        $unit_cost = (10 + (-0.001 * $request->credits + 3) * $request->credits) / $request->credits;
//        Fetch the Client from XERO
        if (!$contact = array_first(XeroPrivate::load('Accounting\\Contact')
            ->where('AccountNumber', $school_id)
            ->execute())){
            return "Could not load Contact from Xero";
        }
        $xero = app()->make('XeroPrivate');
        $invoice = app()->make('XeroInvoice');
        $line1 = app()->make('XeroInvoiceLine');
        $invoice->setContact($contact);
        $invoice->setType('ACCREC');
        $invoice->setCurrencyCode('USD');

// Create some order lines
        $line1->setItemCode('DC_CREDIT');
        $line1->setDescription('Section Credit for Drive Companion');
        $line1->setQuantity($request->credits);
        $line1->setUnitAmount($unit_cost);
        $line1->setTaxAmount(0);
        $line1->setAccountCode('4000');
        $invoice->setDate(Carbon::now());
        $invoice->setDueDate(Carbon::now()->addDays(30));

// Add the line to the order
        $invoice->addLineItem($line1);
        $invoice->setStatus('AUTHORISED');

        if($xero->save($invoice)){
            $school->credits += $request->credits;
            $school->save();

//            Get the Online Link for the Invoice
            $url = new \XeroPHP\Remote\URL($xero, sprintf('%s/%s/OnlineInvoice', $invoice->getResourceURI(), $invoice->getInvoiceID()));
            $req = new \XeroPHP\Remote\Request($xero, $url);
            $req->send();
            $online_link = $req->getResponse()->getElements()[0]["OnlineInvoiceUrl"];

            // Get the invoice PDF
            $invoice_pdf = $invoice->getPdf();
//            Email the Invoice
            Mail::send('emails.invoice', ['contact' => $contact, 'invoice' => $invoice,'online_link' => $online_link], function ($m) use ($contact, $invoice, $invoice_pdf) {
                $m->from('adriaan@engagenie.com', 'Engagenie');
                $m->to($contact->EmailAddress, $contact->FirstName." ".$contact->LastName)->subject('New Invoice ('.$invoice->InvoiceNumber.')');
                $m->attachData($invoice_pdf, $invoice->InvoiceNumber.".pdf");
            });
            return $school;
        } else {
            return "Could not save the Credits.";
        }
    }
    /*
     *  Generate a new code and save it in the database
     */
    public function setCode($school_id)
    {
        $characters="abcdefghijklmnopqrstuvwxyz";
        $code="";
        for ($i=0; $i<=5; $i++){
            $code .= $characters[rand(0, strlen($characters)-1)];
        }
        $school = School::find($school_id);
        $school->code=$code;
        $school->save();
        return $code;
    }
    public function deleteCode($school_id)
    {
        $school = School::find($school_id);
        $school->code=null;
        $school->save();
        return null;
    }

}
