<% require themedCSS(KBArticleList) %>

<% if BestArticles %>
    <div class="FeaturedArticles ArticleSection">
        <h2><% _t('TOP_RATED_ARTICLES', 'Top Rated Articles') %></h2>
        <ul class="ArticleList">
            <% control BestArticles %>
                <% include KBArticleLink %>
            <% end_control %>
        </ul>
    </div>
<% end_if %>