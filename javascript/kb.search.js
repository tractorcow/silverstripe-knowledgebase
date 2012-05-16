/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery.noConflict();

(function($){
    $(function(){
        $(".ArticleSearch").each(function(){
            var action = $('form',this).attr('action');
            var field = $("#SearchText input.text", this);
            field.autocomplete({
                minLength: 0,
                source: action,
                focus: function( event, ui ) {
                    field.val( ui.item.label );
                    return false;
                },
                select: function( event, ui ) {
                    window.location = ui.item.link;
                    return false;
                }
            })
            .data( "autocomplete" )._renderItem = function( ul, item ) {
                return $( "<li></li>" )
                .data( "item.autocomplete", item )
                .append( "<a href='" + item.link + "'><span class='ac-label'>" + item.label + "</span><br /><span class='ac-desc'>" + item.description + "</span> <span class='ac-rating'>"+item.rating+"</span></a>" )
                .appendTo( ul );
            };
        });
    });
})(jQuery);