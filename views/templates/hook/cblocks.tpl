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
{if isset($category) && $category}
    <div id="idnkcsp-block">
        {foreach from=$category item=c}
            <div class="idnkcsp-container">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <h1 style="color:{$c.category_color}" class="idnkcsp-category-name">{$c.category_name}</h1>
                    {if $c.category_children}
                        <div class="idnkcsp-link-content">
                            {foreach from=$c.category_children item="children"}
                                {if $children@iteration > 15}{break}{/if}
                                <li class="idnkcsp-link" style="border-color:{$c.category_color}"><a href="{$link->getCategoryLink($children.id_category)}">{$children.name}</a></li>
                            {/foreach}
                        </div>
                    {/if}
                </div>
                <div class="row aligned-row">
                    <div class="col-md-4 hidden-md-down col-lg-4 idnkcsp-product-list">
                        <img loading="lazy" class="idnkcsp-background" src="{$c.category_image}" />
                    </div>
                    <div class="col-sm-12 col-md-8 col-lg-8 idnkcsp-grid">
                        {foreach from=$c.category_product item="product"}
                            {include file=$product_template product=$product}
                        {/foreach}
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
{/if}
