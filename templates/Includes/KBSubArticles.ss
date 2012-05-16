<% require themedCSS(KBArticleList) %>

<% if SubArticles %>
    <div class="ArticleSection">
        <h2><% _t('ARTICLES', 'Articles') %></h2>
        <ul class="ArticleList">
            <% control SubArticles %>
                <% include KBArticleLink %>
            <% end_control %>
        </ul>
    </div>
<% end_if %>