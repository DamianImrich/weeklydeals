<div class="item {if isset($class_item)}{$class_item}{/if} text-center">
        <div class="product-description">
            {block name='product_name'}
                <div class="product-title" itemprop="name"><a href="{$product.url}">{$product.name|truncate:30:'...'}</a></div>
            {/block}
            <div class="d-flex product-groups">
                <div class="product-desc-left text-left w-60">
                    <div class="product-group-price">
                        {block name='product_price_and_shipping'}
                            {if $product.show_price}
                                <div class="product-price-and-shipping">

                                    {hook h='displayProductPriceBlock' product=$product type="before_price"}

                                    <span itemprop="price" class="price">{$product.price}</span>

                                    {if $product.has_discount}
                                        {hook h='displayProductPriceBlock' product=$product type="old_price"}

                                        <span class="regular-price">{$product.regular_price}</span>
                                        {if $product.discount_type === 'percentage'}
                                          <span class="discount-percentage">{$product.discount_percentage}</span>
                                        {/if}
                                    {/if}
                                </div>
                            {/if}
                        {/block}
                    </div>
                </div>
            </div>
        </div>
        <div class="thumbnail-container">
            {block name='product_thumbnail'}
                {if isset($is_category) && !empty($is_category)}
                    <a href="{$product.url}" class="thumbnail product-thumbnail">
                        <img
                                class="img-fluid image-cover"
                                src="{$product.cover.bySize.home_default.url}"
                                alt="{$product.cover.legend}"
                                data-full-size-image-url="{$product.cover.large.url}"
                                width="{$product.cover.bySize.home_default.width}"
                                height="{$product.cover.bySize.home_default.height}"
                        >
                    </a>
                {else}
                    <a href="{$product.url}" class="thumbnail product-thumbnail">
                        <img
                                class="img-fluid image-cover"
                                src = "{$product.cover.bySize.home_default.url}"
                                alt = "{$product.cover.legend}"
                                data-full-size-image-url = "{$product.cover.large.url}"
                                width="{$product.cover.bySize.home_default.width}"
                                height="{$product.cover.bySize.home_default.height}"
                        >
                    </a>
                {/if}
            {/block}



        </div>
</div>