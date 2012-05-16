<% require themedCSS(KBArticleList) %>

<% if ChildArticles %>
    <div class="ArticleSection">
        <h2><% _t('ARTICLES', 'Articles') %></h2>
        <ul class="ArticleList">
            <% control ChildArticles %>
                <% include KBArticleLink %>
            <% end_control %>
        </ul>
    </div>
<% end_if %>