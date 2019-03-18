<section class="background_wrapper weekly-deal-wrapper">
    <div class="container">
        <h1 class="title_block text-center mb-0">{l s='Products of the week' mod='weeklydeals'}</h1>
        <p class="subTitle">{l s='Get new cheap deal every day ' mod='weeklydeals'}</p>

        <div class="expiresWrapper">
            <p class="expiresTitle">{l s='Expires in' mod='weeklydeals'}</p>
            <p id="weeklydealCountdown">{$countdown}</p>
        </div>
    </div>
    <div
            class="products product_list grid owl-carousel owl-theme"
            data-autoplay="true"
            data-autoplayTimeout="6000"
            data-loop="true"
            data-items="4"
            data-items_tablet="3"
            data-items_mobile="2"

            data-margin="0"
            data-margin_mobile="0"
            data-margin_tablet="0"

            data-nav="true"
            data-dots="false"

    >
        {if isset($presentedProducts) && !empty($presentedProducts)}
            {include file='_partials/layout/items/item_one.tpl' products=$presentedProducts class_item='chess' number_row=1}
        {else}
            <p class="alert alert-info text-center w-100">{l s='No products at this time.'}</p>
        {/if}
    </div>
</section>

<script type="text/javascript">
   
    var elem = document.getElementById("weeklydealCountdown");
		var countdown = elem.innerHTML.split(":");
		
    setInterval(everySecond, 1000);

    function everySecond() {
			countdown[3]--;
			if(countdown[3]==-1){
				countdown[3] = 59
				countdown[2]--
				
				if(countdown[2]==-1){
					countdown[2] = 59
					countdown[1]--
				}
			}

			var days = (countdown[0] > 1) ? ((countdown[0] > 4) ? ' dní ' : ' dni ') : (countdown[0] > 0 ? ' deň ' : ' dní ');
			var hours = (countdown[1] > 1) ? ((countdown[1] > 4) ? ' hodín ' : ' hodiny ') : (countdown[1] > 0 ? ' hodina ' : ' hodín ');
			var minutes = (countdown[2] > 1) ? ((countdown[2] > 4) ? ' minút ' : ' minúty ') : (countdown[2] > 0 ? ' minúta ' : ' minút ');
			var seconds = (countdown[3] > 1) ? ((countdown[3] > 4) ? ' sekúnd ' : ' sekundy ') : (countdown[3] > 0 ? ' sekunda ' : ' sekúnd ');

      elem.innerHTML = countdown[0]+days+countdown[1]+hours+ countdown[2]+minutes+ countdown[3]+seconds
    }
</script>