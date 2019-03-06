<section class="background_wrapper weekly-deal-wrapper">
    <div class="container">
        <h1 class="page-main-title text-center text-white mb-0">{l s='Products of the week' mod='weeklydeals'}</h1>
        <p class="subTitle">{l s='Get new cheap deal every day ' mod='weeklydeals'}</p>

        {foreach from=$products item=prdct}
        <div class="productWrapper js-anchor" data-anchor="{$prdct.url}">
            <div class="product-title" itemprop="name"><a href="{$prdct.url}">{$prdct.name|truncate:30:'...'}</a>
            </div>
            <div class="product-group-price">
                {block name='product_price_and_shipping'}
                    {if $prdct.show_price}
                        <div class="product-price-and-shipping">

                            <div class="currentPriceWrapper">
                                <div
                                        class="product-price {if $prdct.has_discount}has-discount{/if}"
                                        itemprop="offers"
                                        itemscope
                                        itemtype="https://schema.org/Offer"
                                >
                                    <link itemprop="availability" href="https://schema.org/InStock"/>
                                    <meta itemprop="priceCurrency" content="{$currency.iso_code}">

                                    <div class="current-price">
                                        {hook h='displayProductPriceBlock' product=$product type="before_price"}
                                        <span itemprop="price" class="price">{$prdct.price}</span>
                                    </div>
                                </div>
                                <div class="tax-shipping-delivery-label">
                                    {if $configuration.display_taxes_label}
                                        {$prdct.labels.tax_long}
                                    {/if}
                                    {hook h='displayProductPriceBlock' product=$product type="price"}
                                    {hook h='displayProductPriceBlock' product=$product type="after_price"}
                                </div>
                            </div>


                            <div class="discountWrapper">
                                {if $prdct.has_discount}
                                    {if $prdct.discount_type === 'percentage'}
                                        <span class="discount discount-percentage">- {$prdct.discount_percentage_absolute}</span>
                                    {else}
                                        <span class="discount discount-amount">
                                          - {$prdct.discount_to_display}
                                        </span>
                                    {/if}
                                {/if}
                                {block name='product_discount'}
                                    {if $prdct.has_discount}
                                        <div class="product-discount">
                                            {hook h='displayProductPriceBlock' product=$product type="old_price"}
                                            <span class="regular-price">{$prdct.regular_price}</span>

                                        </div>
                                    {/if}
                                {/block}
                            </div>
                        </div>
                    {/if}
                {/block}
            </div>
            <div class="thumbnail-container">
                <a href="{$prdct.url}" class="thumbnail product-thumbnail">
                    <img
                            class="img-fluid image-cover"
                            src="{$prdct.cover.bySize.home_default.url}"
                            alt="{$prdct.cover.legend}"
                            data-full-size-image-url="{$prdct.cover.large.url}"
                            width="{$prdct.cover.bySize.home_default.width}"
                            height="{$prdct.cover.bySize.home_default.height}"
                    >
                </a>
            </div>

        </div>
        {/foreach}
        {*{block name='product_buy'}
            <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
                <input type="hidden" name="token" value="{$static_token}">
                <input type="hidden" name="id_product" value="{$prdct.id}" id="product_page_product_id">
                {if isset($prdct.id_customization)}
                    <input type="hidden" name="id_customization" value="{$prdct.id_customization}"
                           id="product_customization_id">
                {/if}


                <div class="product-add-to-cart">
                    {if !$configuration.is_catalog}

                        {block name='product_quantity'}
                            <div class="product-quantity">

                                <div class="add">
                                    <button class="btn btn-secondary add-to-cart" data-button-action="add-to-cart"
                                            type="submit"
                                            {if !$prdct.add_to_cart_url || $prdct.quantity_wanted > $prdct.quantity}disabled{/if}>
                                        <span>{l s='Add to cart' d='Shop.Theme.Actions'}</span>
                                        <i class="icon-boozer_kosik"></i>
                                    </button>

                                    <div class="qtyWrapper">
                                        <span class="quantityControls" data-value="-1"><i
                                                    class="fa fa-minus"></i></span>
                                        <div class="qty">
                                            <input
                                                    type="number"
                                                    name="qty"
                                                    id="quantity_input"
                                                    value="{$prdct.quantity_wanted}"
                                                    class="input-group"
                                                    min="{$prdct.minimal_quantity}"
                                                    data-weeklydeal="true"
                                            />
                                        </div>
                                        <span class="quantityControls" data-value="1"><i class="fa fa-plus"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        {/block}
                    {/if}
                </div>


            </form>
        {/block}*}
        <div class="expiresWrapper">
            <p class="expiresTitle">{l s='Expires in' mod='weeklydeals'}</p>
            <p id="weeklydealCountdown">{$countdown}</p>
        </div>
    </div>
</section>

<script type="text/javascript">
    function calculateHMSleft() {
        var now = new Date();
        var hoursleft = 23 - now.getHours();
        var minutesleft = 59 - now.getMinutes();
        var secondsleft = 60 - now.getSeconds();

        if (minutesleft < 10) minutesleft = "0" + minutesleft;
        if (secondsleft < 10) secondsleft = "0" + secondsleft;

        return hoursleft + ":" + minutesleft + ":" + secondsleft;
    }

    var elem = document.getElementById("weeklydealCountdown");

    setInterval(everySecond, 1000);

    function everySecond() {
        elem.innerHTML = calculateHMSleft();
    }
</script>