/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery.noConflict();

(function($){
    $(function(){
        $(".RatingControl").each(function(){
            var postback = $(this).attr('data-postback');
            $(this).jRating({
                /** String vars **/
                bigStarsPath : 'knowledgebase/images/Rating/stars.png',
                smallStarsPath : 'knowledgebase/images/Rating/small.png',
                phpPath : postback,
                type : 'big', // can be set to 'small' or 'big'
			
                /** Integer vars **/
                length: 5,
                rateMax : 10,
			
                /** Functions **/
                onSuccess : null,
                onError : null
            });
        });
    });
})(jQuery);