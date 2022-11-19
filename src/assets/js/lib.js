var all_props = [
    'label',
    'place_holder',
    'helper_text',
    'default_value',
    'required',
    'search_index',
    'max_length',
    'min_length',
    'max',
    'min',
    'apply_separator',
    'max_size',
    'calendar_type',
    'number_type',
    'custom_data_source'
];
var props_per_type = {
    // TYPE_TEXT_INPUT
    '1'  : ['label', 'place_holder', 'helper_text', 'default_value', 'required', 'max_length', 'min_length'],
    //TYPE_NUMBER_INPUT
    '2'  : ['label', 'place_holder', 'helper_text', 'default_value', 'required', 'max', 'min', 'number_type'],
    // TYPE_CHECKBOX_INPUT
    '3'  : ['label', 'helper_text'],
    // TYPE_CHECKBOX_GROUP_INPUT
    '4'  : ['label', 'helper_text', 'required'],
    // TYPE_RADIO_GROUP_INPUT
    '5'  : ['label', 'helper_text', 'required', 'default_value'],
    // TYPE_TEXT_AREA_INPUT
    '6'  : ['label', 'place_holder', 'helper_text', 'default_value', 'required', 'max_length', 'min_length'],
    // TYPE_MOBILE_INPUT
    '7'  : ['label', 'place_holder', 'helper_text', 'required'],
    // TYPE_PHONE_INPUT
    '8'  : ['label', 'place_holder', 'helper_text', 'required'],
    // TYPE_NATIONAL_CODE_INPUT
    '9'  : ['label', 'place_holder', 'helper_text', 'required'],
    // TYPE_IMAGE_UPLOAD_INPUT
    '10' : ['label', 'helper_text', 'required', 'max_size'],
    // TYPE_DROP_DOWN_INPUT
    '11' : ['label', 'helper_text', 'required', 'default_value'],
    // TYPE_DATE_INPUT
    '12' : ['label', 'helper_text', 'required', 'calendar_type'],
    // TYPE_PLATE_INPUT
    '13' : ['label', 'place_holder', 'helper_text', 'required'],
    // TYPE_PRICE_INPUT
    '14' : ['label', 'place_holder', 'helper_text', 'default_value', 'required', 'max', 'min', 'apply_separator'],
    // TYPE_EMAIL_INPUT
    '15' : ['label', 'place_holder', 'helper_text', 'default_value', 'required'],
    // TYPE_URL_INPUT
    '16' : ['label', 'place_holder', 'helper_text', 'default_value', 'required'],
    // TYPE_CUSTOM_DATA_SOURCE_INPUT
    '17' : ['label', 'place_holder', 'helper_text', 'required', 'custom_data_source'],
}

function resetFields(element){
    type = $(element).val();
    prefix_id = $(element).attr('id').replace('type', '');
    prefix_class = 'field-' + prefix_id;
    diff_props = $(all_props).not(props_per_type[type]).get();
    formSetting = $(element).closest('form');

    $.each(diff_props, function(index, prop){
        formSetting.find('.' + prefix_class + prop).css('display', 'none');
        if($.inArray( prop, ['required', 'search_index', 'apply_separator'] ) > -1) {
            formSetting.find('#' + prefix_id + prop).removeAttr('checked').prop('checked', false);
        }else{
            formSetting.find('#' + prefix_id + prop).val('');
        }
    });

    $.each(props_per_type[type], function(index, prop){
        formSetting.find('.' + prefix_class + prop).css('display', 'block');
    });

    if($.inArray( type, ["4", "5", "11"] ) > -1){
        formSetting.find('.options').css('display', 'block');
    }else{
        formSetting.find('.options').css('display', 'none');
        formSetting.find('.options').find(':input:text').val('');
        formSetting.find('.options').find(':checkbox, :radio').prop('checked',false).removeAttr('checked');
        formSetting.find('.options').find('.remove-option').trigger('click');
    }
    formSetting.yiiActiveForm('resetForm');
}