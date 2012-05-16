<% require themedCSS(KBArticleList) %>

<% if FeaturedArticles %>
    <div class="FeaturedArticles ArticleSection">
        <h2><% _t('FEATURED_ARTICLES', 'Featured Articles') %></h2>
        <ul class="ArticleList">
            <% control FeaturedArticles %>
                <% include KBArticleLink %>
            <% end_control %>
        </ul>
    </div>
<% end_if %>