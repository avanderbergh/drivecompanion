<div class="panel panel-primary" v-if="buyCreditsForm.show">
    <div class="panel-heading">
        <h3 class="panel-title">Buy Credits</h3>
    </div>
    <div class="panel-body">
        <form class="form-inline">
            <div class="form-group">
                <label for="add_credits">Number of Credits</label>
                <div class="input-group">
                    <input type="number" min="5" step="5" name="add_credits" required="required" value="5" placeholder="Enter an Amount" class="form-control" v-model="credits.add">
                    <div class="input-group-addon"> ≈ @{{ credits.add | bulkpriceper | currency }} each</div>
                </div>
            </div>
            <div class="form-group">
                <button type="button" v-on:click="cancelBuyCredits()" class="btn btn-default">Cancel</button>
                <button type="button" v-on:click="buyCredits()" :disabled="credits.add < 5 || credits.add > 1000 || buyCreditsForm.disabled" class="btn btn-success">Buy Now for @{{ credits.add | bulkprice | currency }}</button>
            </div>
            <div>
                <p class="text-muted">
                    <small>A minimum of 5 and a maximum of 1000 Credits can be purchased at one time. All prices are in USD.</small>
                </p>
            </div>
        </form>
    </div>
</div>
