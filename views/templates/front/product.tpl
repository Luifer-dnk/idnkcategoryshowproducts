{*
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author IDNK Soft <i@dnk.software>
 * @copyright  2021-2022 IDNK Soft
 * @license    Valid for 1 website (or project) for each purchase of license
*}
{if $page.page_name == 'index'}
    {if isset($pte_homeproducts) && $pte_homeproducts == 3}{assign var=col value=4}{/if}
    {if isset($pte_homeproducts) && $pte_homeproducts == 4}{assign var=col value=3}{/if}
    {if isset($pte_homeproducts) && $pte_homeproducts == 5}{assign var=col value=2}{/if}
{elseif $page.page_name == 'category' || $page.page_name == 'new-products' || $page.page_name == 'prices-drop' || $page.page_name == 'best-sales'}
    {if isset($smarty.cookies.listproducts) && $smarty.cookies.listproducts == 3}{assign var=col value=4}{/if}
    {if isset($smarty.cookies.listproducts) && $smarty.cookies.listproducts == 4}{assign var=col value=3}{/if}
    {if isset($smarty.cookies.listproducts) && $smarty.cookies.listproducts == 5}{assign var=col value=2}{/if}
{else}
    {assign var=col value=2}
{/if}

{block name='product_miniature_item'}
    <article class="product-miniature js-product-miniature col-6 col-sm-4 col-md-11"
             data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}">
        <div class="product-block {if $product.new}new-product{/if}">

            <div class="thumbnail-container position-relative h-auto overflow-hidden">
                {block name='product_flags'}
                    {foreach from=$product.flags item=flag}
                        {if $flag.type == 'new'}
                            <span class="{$flag.type} d-flex align-items-center position-absolute font-weight-bold text-uppercase px-2">{l s='New' d='Shop.CreathemeCatalog'}</span>
                        {/if}
                    {/foreach}
                {/block}

                {if $product.show_price && $product.has_discount}
                    {if $product.discount_type === 'percentage'}
                        <span class="discount-percentage discount d-flex justify-content-center align-items-center position-absolute rounded-circle px-2">
              {$product.discount_percentage}
            </span>
                    {elseif $product.discount_type === 'amount'}
                        <span class="discount-amount discount d-flex justify-content-center align-items-center position-absolute rounded-circle px-2">
              -{$product.discount_amount}
            </span>
                    {/if}
                {/if}

                {block name='product_thumbnail'}
                    <span class="product-thumbnail" data-ob="{$product.url|base64_encode}">
            {assign var=cover value=(isset($product.default_image)) ? $product.default_image : $product.cover}
                        {if $cover}
                            <img class="product-img lazy {if isset($pte_productimgborder) && $pte_productimgborder}img-thumbnail{/if}"
                                 src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                 data-src="{$cover.bySize.home_default.url}" data-full-size-image-url="{$cover.bySize.large_default.url}"
                                 alt="{if $cover.legend}{$cover.legend}{else}{$product.name}{/if}" decoding="async"
                                 width="{$cover.bySize.home_default.width}" height="{$cover.bySize.home_default.height}">
              {foreach from=$product.images item=image}
                            {if $image != $cover}
                                <img class="product-img-alt lazy {if isset($pte_productimgborder) && $pte_productimgborder}img-thumbnail{/if} position-absolute"
                                     src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                     data-src="{$image.bySize.home_default.url}" data-full-size-image-url="{$image.bySize.large_default.url}"
                                     alt="{if $image.legend}{$image.legend}{else}{$product.name}{/if}" decoding="async"
                                     width="{$image.bySize.home_default.width}" height="{$image.bySize.home_default.height}">
                                {break}
                            {/if}
                        {/foreach}
            {elseif isset($urls.no_picture_image)}
              <img class="lazy {if isset($pte_productimgborder) && $pte_productimgborder}img-thumbnail{/if}"
                   src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                   data-src="{$urls.no_picture_image.bySize.home_default.url}" alt="" decoding="async"
                   width="{$urls.no_picture_image.bySize.home_default.width}" height="{$urls.no_picture_image.bySize.home_default.height}">
                        {/if}
          </span>
                {/block}

                <div class="highlighted-informations position-absolute w-100 h-auto">
                    {block name='product_variants'}
                        {if $product.main_variants}
                            {include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}
                        {/if}
                    {/block}

                    {block name='product_availability'}
                        {if $product.show_availability && $product.available_for_order}
                            <div class="{if $product.quantity <= 0 && $product.allow_oosp}last_remaining_items{else}{$product.availability}{/if}
                          product-availability font-weight-bold text-center text-uppercase {if $product.main_variants}mt-1 mb-2 mx-2{else}m-2{/if}">
                                {if $product.availability_message}
                                    {assign var=inCart value=false}
                                    {foreach from=$cart.products item=cartproduct}
                                        {if $cartproduct.id_product == $product.id_product && $cartproduct.id_product_attribute == $product.id_product_attribute}
                                            {assign var=inCart value=true}
                                        {/if}
                                    {/foreach}
                                    {if $inCart == true && $product.quantity <= 1 && !$product.allow_oosp}
                                        {l s='The last one available is in your cart' d='Shop.CreathemeCatalog'}
                                    {else}
                                        {$product.availability_message}
                                    {/if}
                                {else}
                                    {if $product.quantity <= 0 && $product.allow_oosp}
                                        {l s='Preorder' d='Shop.CreathemeCatalog'}
                                    {elseif $product.quantity <= 0 && !$product.allow_oosp}
                                        {l s='Out of stock' d='Shop.CreathemeCatalog'}
                                    {else}
                                        {l s='Available' d='Shop.CreathemeCatalog'}
                                    {/if}
                                {/if}
                            </div>
                        {elseif $product.show_availability && !$product.available_for_order}
                            <div class="unavailable product-availability font-weight-bold text-center text-uppercase
                          {if $product.main_variants}mt-1 mb-2 mx-2{else}m-2{/if}">{l s='Unavailable' d='Shop.CreathemeCatalog'}</div>
                        {/if}
                    {/block}
                </div>
            </div>

            <div class="info-container w-100">
                {block name='product_reviews'}{hook h='displayProductListReviews' product=$product}{/block}

                {block name='product_name'}
                    <h3 class="product-title h5 text-center overflow-hidden mt-2">
                        <a href="{$product.url}">{$product.name|truncate:45:'...'}</a>
                    </h3>
                {/block}

                {block name='product_description_short'}
                    <div class="product-description-short overflow-hidden hidden mb-2">{$product.description_short|strip_tags|truncate:180:'...'}</div>
                {/block}

                {block name='product_price_and_shipping'}
                    {if $product.show_price}
                        <div class="product-price-and-shipping d-flex justify-content-between align-items-end">

                            <div class="product-price-shipping">
                                {if $product.has_discount}
                                    <span class="regular-price d-block" aria-label="{l s='Regular price' d='Shop.CreathemeCatalog'}">{$product.regular_price}</span>
                                {/if}

                                {hook h='displayProductPriceBlock' product=$product type='before_price'}
                                <span class="price" aria-label="{l s='Price' d='Shop.CreathemeCatalog'}">
                  {capture name='custom_price'}{hook h='displayProductPriceBlock' product=$product type='custom_price' hook_origin='products_list'}{/capture}
                                    {if $smarty.capture.custom_price !== ''}
                                        {$smarty.capture.custom_price nofilter}
                                    {else}
                                        {$product.price}
                                    {/if}
                </span>

                                {hook h='displayProductPriceBlock' product=$product type='unit_price'}
                                {hook h='displayProductPriceBlock' product=$product type='weight'}
                            </div>

                            <div class="product-actions d-flex" style="display: none">
                                {if !$configuration.is_catalog && (($page.page_name == 'index' && (!isset($pte_homebutton) || $pte_homebutton)) || ($page.page_name != 'index' && (!isset($smarty.cookies.listbutton) || $smarty.cookies.listbutton)))}
                                    {if $product.add_to_cart_url != null && $product.available_for_order && $product.customizable == 0 && ($product.quantity > 0 || $product.allow_oosp)
                                    && ($product.minimal_quantity <= 1 && (!isset($product.product_attribute_minimal_quantity) || $product.product_attribute_minimal_quantity <= 1))}
                                        <form method="post" action="{url entity='cart'}">
                                            <input name="token" type="hidden" value="{if isset($smarty.cookies.statictoken)}{$smarty.cookies.statictoken}{/if}">
                                            <input name="id_product" type="hidden" value="{$product.id_product}">
                                            <button class="add-to-cart btn shadow-none p-0 {if $ps_version >= '1.7.8.0'}add-to-cart-fix{/if}" type="submit" data-button-action="add-to-cart" data-toggle="tooltip" title="{l s='Add to cart' d='Shop.CreathemeCatalog'}"
                                                    aria-label="{l s='Add to cart' d='Shop.CreathemeCatalog'}">{if $ps_version < '1.7.8.0'}<i class="icon-shopping-basket-round" aria-hidden="true"></i>{/if}</button>
                                        </form>
                                    {else}
                                        <button class="btn disabled p-0" type="button" data-toggle="tooltip" title="{l s='Add to cart' d='Shop.CreathemeCatalog'}"
                                                aria-label="{l s='Add to cart' d='Shop.CreathemeCatalog'}"><i class="icon-shopping-basket-round" aria-hidden="true"></i></button>
                                    {/if}
                                {/if}

                                {block name='quick_view'}
                                    {if !isset($pte_quickview) || $pte_quickview}
                                        <button class="quick-view js-quick-view btn shadow-none hidden-xs-down ml-2 p-0" type="button" data-link-action="quickview" data-toggle="tooltip" title="{l s='Quick view' d='Shop.CreathemeCatalog'}"
                                                aria-label="{l s='Quick view' d='Shop.CreathemeCatalog'}"><i class="icon-magnifier" aria-hidden="true"></i></button>
                                    {/if}
                                {/block}
                            </div>

                        </div>
                    {/if}
                {/block}
            </div>

        </div>
    </article>
{/block}
