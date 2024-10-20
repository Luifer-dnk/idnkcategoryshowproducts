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
<link rel="stylesheet" type="text/css" href="../modules/idnkcategoryshowproducts/views/css/spectrum.min.css" />
<script type="text/javascript" src="../modules/idnkcategoryshowproducts/views/js/spectrum.min.js"></script>
<style>
    .preview-side{
        height:300px;
        width:5px;
        background-color:red;
        display:inline-block;
        margin-right:-3px;
    }

    #category-preview{
        display:inline-block;
        vertical-align:top;
        height:300px !important;
    }
    #category-img{
        width:auto !important;
        max-height:300px !important;
        max-width:180px;
        display:none;
        vertical-align:top;
    }

    #category-img-preview{
        max-width:150px;
        vertical-align:top;
    }

    .idnkcsp-preview{
        position:relative;
        max-height:300px;
        overflow:hidden;
    }

    .preview-text{
        position: absolute;
        top: 40%;
        left: 2%;
        font-weight: 700;
        font-size: 16pt;
    }
</style>

<div class="panel preview_section">
    <div class="panel-heading">
        <i class="icon-eye"></i> Block preview
    </div>
    <div class="form-wrapper idnkcsp-preview">
        <span class="preview-text">Category</span>
        <img id="category-img-preview" src="
        {$base_url}modules/idnkcategoryshowproducts/views/img/{$image_url}
        " alt="Preview" />
        <img id="category-preview" src="{$base_url}modules/idnkcategoryshowproducts/views/img/preview.jpg" alt="Preview" />
        <img id="category-img" src="#" alt="Select category block image" />
    </div>
</div>



<form action="{$link->getAdminLink('AdminCblocks')|escape:'htmlall':'UTF-8'}" method="post" class="form-horizontal" autocomplete="off" enctype="multipart/form-data">
    <div class="panel edit_page_section">
        <div class="panel-heading">
            <i class="icon-pencil"></i> CATEGORY: {$name}
        </div>
        <div class="form-wrapper">
            <div class="form-group">
                <div class="col-lg-6">
                    <input type="hidden" name="category_id" value="{$id}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">{l s="Select category block image" mod="idnkcategoryshowproducts"}</label>
                <div class="col-lg-6">
                    <input type="hidden" name="MAX_FILE_SIZE" value="{$max_upload_size}" />
                    <input type="file" accept="image/x-png,image/gif,image/jpeg" class="required form-control" id="category_img" name="category_img" onchange="readURL(this);"/>
                    <p class="help-block">
                        Recommended size 360x700 (px). Maximum upload size: {math equation="size / mb" size=$max_upload_size mb=1000000 format="%.2f"}Mb
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">Category text color<sup style="color:red">*</sup></label>
                <div class="col-lg-3">
                    <input type="text" id="color-picker" name="category_color" maxlength="7" value="{$color}" />
                    <p class="help-block">Click in the textfield to choose color</p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">Shadow Category text color<sup style="color:red">*</sup></label>
                <div class="col-lg-3">
                    <input type="text" id="shadow-color-picker" name="category_shadow" maxlength="7" value="{$shadow}" />
                    <p class="help-block">Click in the textfield to choose shadow color</p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">Size for text<sup style="color:red">*</sup></label>
                <div class="col-lg-3">
                    <input type="text" id="txtsize" name="category_txtsize" maxlength="7" value="{$txtsize}" />
                    <p class="help-block">Text Size (e.g., 26px)</p>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <a href="{$link->getAdminLink('AdminModules', false)}&configure=idnkcategoryshowproducts&token={Tools::getAdminTokenLite('AdminModules')}" class="btn btn-default">
            <i class="process-icon-back"></i> {l s="Back" mod="idnkcategoryshowproducts"}
        </a>
        <button type="submit" value="1" id="cms_form_submit_btn" name="saveCategoryData" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s="Save" mod="idnkcategoryshowproducts"}
        </button>
    </div>

    </div>

<script>
    var initialColor = "{$color}";
    var initialShadowColor = "{$shadow}";

    // Aplicar el color inicial
    $(".preview-side").css("background-color", initialColor);
    $(".preview-text").css("color", initialColor);
    $(".preview-text").css("text-shadow", "2px 2px " + initialShadowColor);

    // Funciones para actualizar colores
    function updateColor(element, color) {
        $(element).css("background-color", (color ? color.toHexString() : ""));
    }

    function updateTextColor(element, color) {
        $(element).css("color", (color ? color.toHexString() : ""));
    }

    function updateShadowColor(element, color) {
        $(element).css("text-shadow", "2px 2px " + (color ? color.toHexString() : ""));
    }

    // Color picker para el texto
    $('#color-picker').spectrum({
        type: "component",
        showAlpha: false,
        clickoutFiresChange: true,
        preferredFormat: 'hex',
        showSelectionPalette: true,
        showInput: false,
        move: function (color) {
            updateColor(".preview-side", color);
            updateTextColor(".preview-text", color);
        },
        hide: function (color) {
            updateColor(".preview-side", color);
            updateTextColor(".preview-text", color);
        }
    });

    // Color picker para la sombra
    $('#shadow-color-picker').spectrum({
        type: "component",
        showAlpha: false,
        clickoutFiresChange: true,
        preferredFormat: 'hex',
        showSelectionPalette: true,
        showInput: false,
        move: function (color) {
            updateShadowColor(".preview-text", color);
        },
        hide: function (color) {
            updateShadowColor(".preview-text", color);
        }
    });

    // Ajustes de estilo
    $('.sp-colorize').css('height', '30px');
    $('.sp-colorize').css('width', '30px');
    $('input.spectrum.with-add-on').css('padding-left','10px');
</script>

    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                $('#category-img-preview').css('display','none');
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#category-img')
                        .attr('src', e.target.result)
                        .css('display', 'inline-block')
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        function readIconURL(input) {
            if (input.files && input.files[0]) {

                var reader = new FileReader();

                reader.onload = function (e) {
                    $('.icon-img-box')
                        .css('display', 'inline-block'),
                        $('.icon-img')
                            .attr('src', e.target.result)
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>


