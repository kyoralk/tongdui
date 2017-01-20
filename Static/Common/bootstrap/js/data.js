/**
 * Created by Administrator on 2017/1/14.
 */
$(function()
{

    /*
     define a new language named "custom"
     */



    $('#date-range1').dateRangePicker(
        {
            startOfWeek: 'monday',
            separator : ' ~ ',
            format: 'YYYY-MM-DD HH:mm',
            autoClose: false,
            time: {
                enabled: true
            }
        });


});
