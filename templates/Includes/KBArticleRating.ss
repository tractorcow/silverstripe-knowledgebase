<% if RatingEnabled %>
    <% require themedCSS(KBArticleRating) %>

    <h4><% _t('RATE_THIS_ARTICLE', 'Rate this article') %></h4>
    <div class="ArticleRatingControl">
        <div class="Message"><% _t('HOW_USEFUL_IS_THIS_ARTICLE', 'How useful is this article?') %></div>
        <div class="RatingControl" id="{$Rating}_$ID" data-postback="$Link(rate)"></div>
    </div>
<% end_if %>