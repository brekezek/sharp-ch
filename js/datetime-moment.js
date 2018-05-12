/**
 * This plug-in for DataTables represents the ultimate option in extensibility
 * for sorting date / time strings correctly. It uses
 * [Moment.js](http://momentjs.com) to create automatic type detection and
 * sorting plug-ins for DataTables based on a given format. This way, DataTables
 * will automatically detect your temporal information and sort it correctly.
 *
 * For usage instructions, please see the DataTables blog
 * post that [introduces it](//datatables.net/blog/2014-12-18).
 *
 * @name Ultimate Date / Time sorting
 * @summary Sort date and time in any format using Moment.js
 * @author [Allan Jardine](//datatables.net)
 * @depends DataTables 1.10+, Moment.js 1.7+
 *
 * @example
 *    $.fn.dataTable.moment( 'HH:mm MMM D, YY' );
 *    $.fn.dataTable.moment( 'dddd, MMMM Do, YYYY' );
 *
 *    $('#example').DataTable();
 */
!function(e){"function"==typeof define&&define.amd?define(["jquery","moment","datatables.net"],e):e(jQuery,moment)}(function(e,n){e.fn.dataTable.moment=function(t,r){var a=e.fn.dataTable.ext.type;a.detect.unshift(function(a){return a&&(a.replace&&(a=a.replace(/(<.*?>)|(\r?\n|\r)/g,"")),a=e.trim(a)),""===a||null===a?"moment-"+t:n(a,t,r,!0).isValid()?"moment-"+t:null}),a.order["moment-"+t+"-pre"]=function(a){return a&&(a.replace&&(a=a.replace(/(<.*?>)|(\r?\n|\r)/g,"")),a=e.trim(a)),n(a,t,r,!0).isValid()?parseInt(n(a,t,r,!0).format("x"),10):1/0}}});