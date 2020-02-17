<?php
/**
 * Dev: Scrap Beast
 * Emai: info@scrapbeast.com
 * Date: 02/13/2020
 * Time: 20:30 AM
 */
only_admin_access();
?>

<?php if (isset($params['backend'])): ?>
    <module type="admin/modules/info"/>
<?php endif; ?>


<script>

$(document).ready(function () {

    $('.js-sb-import-form').submit(function (e) {
        e.preventDefault();

        var js_sb_modal = mw.modal({
            content: '<div id="js-sb-modal">Loading...</div>',
            title: 'Scrap Beast Import Products',
            id: 'js-sb-modal'
        });
        importProducts(0);
    });

});
function importProducts(step = 0) {
    $.ajax({
        url: mw.settings.api_url + 'scrapbeast/import',
        type: 'post',
        data: $('.js-sb-import-form').serialize() + '&step=' + step,
        success: function(data) {
            if (data.message) {
                $('#js-sb-modal').find('.mw_modal_container').append(data.message + '<br />');
            }
            if (data.done) {
                return;
            }
            if (data.success) {
                step = step + 1;
                importProducts(step);
            }
        }
    });
}
</script>

<div id="mw-admin-content" class="admin-side-content">


    <div class="mw-ui-box">
        <div class="mw-ui-box-header">
            <span>Import Products</span>
        </div>
        <div class="mw-ui-box-content">

            <?php  if (isset($messages)):?>
                <?php echo $messages; ?>
                <br >
            <?php endif; ?>

            <form method="post" class="js-sb-import-form">
            <div class="demobox">
                <label class="mw-ui-label">Scrap Beast Live Report Url </label>
                <input type="text" name="live_report_url" value="<?php echo get_option('live_report_url', 'scrapbeast'); ?>" class="mw-ui-field" style="width: 100%">
            </div>
                <br />
            <br />

            <button type="submit" class="mw-ui-btn mw-ui-btn-info">Start Importing</button>
            </form>

        </div>
    </div>

    <div class="mw-ui-box" style="margin-top:20px;">
        <div class="mw-ui-box-header">
            <span>Global pricing rules</span>
        </div>
        <div class="mw-ui-box-content">

          <!--  <label class="mw-ui-check">
                <input type="checkbox" value="pending" name="order_status1" checked="">
                <span></span>
                <span>
                    <b>Advanced pricing rules</b>
                    Set your product markup depending on cost ranges.
                </span>
            </label>
-->

            <style>
                .js-sb-field-rage-seperator {
                    font-size: 18px;
                    line-height: 35px;
                    display: inline-flex;
                    position: relative;
                    width: 15px !important;
                    margin-left: 7px;
                }
                .js-sb-field-range-from {
                    float:left;
                    width: 85px !important;
                }
                .js-sb-field-range-to {
                    float:left;
                    width: 85px !important;
                }
            </style>

            <script>
                $(document).ready(function () {
                    $('.js-sb-tbody-fields').html(sbTemplateTrFields(0, 10));
                    sbValidateTrFields();
                    $('body').on('change', '.js-sb-field-range-from, .js-sb-field-range-to', function() {
                        sbValidateTrFields();
                    });
                });
                function sbValidateTrFields() {
                    $('.js-sb-messages').html('');
                    var countTrFields = 0;
                    $(".js-sb-tr-fields").each(function(i) {
                        countTrFields = countTrFields + 1;
                        var fieldRangeFrom = parseFloat($('.js-sb-tr-fields').eq(i).find('.js-sb-field-range-from').val());
                        var fieldRangeTo = parseFloat($('.js-sb-tr-fields').eq(i).find('.js-sb-field-range-to').val());

                        if (isNaN(fieldRangeFrom) && i == 0) {
                            fieldRangeFrom = 0;
                            $('.js-sb-tr-fields').eq(i).find('.js-sb-field-range-from').val(0);
                        }

                        if (isNaN(fieldRangeTo) && i == 0) {
                            fieldRangeTo = 10;
                            $('.js-sb-tr-fields').eq(i).find('.js-sb-field-range-to').val(10);
                        }

                        if (fieldRangeTo <= fieldRangeFrom) {
                            // $('.js-sb-tr-fields').eq(i).find('.js-sb-field-range-to').css('border', '1px solid red');
                           //  $('.js-sb-tr-fields').eq(i).find('.js-sb-field-range-to').val('');
                            $('.js-sb-messages').html('<div class="mw-ui-box mw-ui-box-content mw-ui-box-important">Cost range end value must be greater than the starting value.</div>');
                            return;
                        }

                        if (isNaN(fieldRangeTo) && isNaN(fieldRangeFrom) && i >= 1) {
                            $('.js-sb-tr-fields').eq(i).remove();
                        }

                        // Check Next Fields
                        var nextRowFieldRangeFrom = parseFloat($('.js-sb-tr-fields').eq(i + 1).find('.js-sb-field-range-from').val());
                        var nextRowFieldRangeTo = parseFloat($('.js-sb-tr-fields').eq(i + 1).find('.js-sb-field-range-to').val());
                        if (isNaN(nextRowFieldRangeFrom) && !isNaN(fieldRangeTo)) {
                            nextRowFieldRangeFrom = fieldRangeTo + 0.01;
                            $('.js-sb-tr-fields').last().after(sbTemplateTrFields(nextRowFieldRangeFrom, ''));
                        }

                        if (!isNaN(nextRowFieldRangeFrom)) {
                            if (fieldRangeTo >= nextRowFieldRangeFrom) {
                                $('.js-sb-messages').html('<div class="mw-ui-box mw-ui-box-content mw-ui-box-important">Your ranges overlap.</div>');
                                $('.js-sb-tbody-fields').html(sbTemplateTrFields(0, 10));
                                sbValidateTrFields();
                                return;
                            }
                        }

                        // Check Previous Fields
                       /* var previousRowFieldRangeFrom = parseFloat($('.js-sb-tr-fields').eq(i).find('.js-sb-field-range-from').val());
                        var previousRowFieldRangeTo = parseFloat($('.js-sb-tr-fields').eq(i).find('.js-sb-field-range-to').val());
                        if (!isNaN(previousRowFieldRangeTo) && !isNaN(fieldRangeFrom) && previousRowFieldRangeTo <= fieldRangeFrom) {
                            alert(previousRowFieldRangeTo);
                            $('.js-sb-messages').html('<div class="mw-ui-box mw-ui-box-content mw-ui-box-important">Your ranges overlap.</div>');
                            return;
                        }*/

                    });
                }
                function sbTemplateTrFields(rangeFrom=0, rangeTo=10) {
                    var html = '<tr class="js-sb-tr-fields"><td>\n' +
                        '                        <div class="mw-field" data-after="USD">\n' +
                        '                            <input type="text" value="'+rangeFrom+'" class="js-sb-field-range-from" placeholder="">\n' +
                        '                        </div>\n' +
                        '                        <div class="js-sb-field-rage-seperator">-</div>\n' +
                        '                        <div class="mw-field" data-after="USD">\n' +
                        '                            <input type="text" value="'+rangeTo+'" class="js-sb-field-range-to" placeholder="">\n' +
                        '                        </div>\n' +
                        '                        <div class="js-sb-field-rage-seperator">X</div>\n' +
                        '                    </td>\n' +
                        '                    <td>\n' +
                        '                        <div class="mw-ui-btn-nav">\n' +
                        '                            <input type="text" class="mw-ui-field" placeholder="">\n' +
                        '                            <div class="mw-dropdown mw-dropdown-default active">\n' +
                        '                                <span class="mw-dropdown-value mw-ui-btn mw-dropdown-val">Multiplier</span>\n' +
                        '                                <div class="mw-dropdown-content">\n' +
                        '                                    <ul>\n' +
                        '                                        <li value="1">Multiplier</li>\n' +
                        '                                        <li value="2">Fixed Markup</li>\n' +
                        '                                    </ul>\n' +
                        '                                </div>\n' +
                        '                            </div>\n' +
                        '                        </div>\n' +
                        '                    </td>\n' +
                        '                    <td>\n' +
                        '                        <a href="javascript:;" class="mw-ui-btn mw-ui-btn-medium">Remove</a>\n' +
                        '                    </td></tr>';
                    return html;
                }
            </script>

            <div class="js-sb-messages" style="margin-bottom:10px;"></div>

            <table class="mw-ui-table table-style-2" width="100%" cellspacing="0" cellpadding="0">
                <thead>
                <tr>
                    <th>Cost range</th>
                    <th>Markup</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody class="js-sb-tbody-fields"></tbody>
            </table>

        </div>
    </div>
</div>
 
