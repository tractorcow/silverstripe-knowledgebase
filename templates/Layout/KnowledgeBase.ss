<div class="KnowledgeBase">
    <div id="Sidebar">
        <% include KBQuickSearch %>
        <% include KBCategoryMenu %>
    </div>
    <div id="Content">
        <% include BreadCrumbs %>
        <h1>$Title.XML</h1>
        <% if Content %>
            <div class="Copy typography">
                $Content
            </div>
        <% end_if %>
        <% include KBCategories %>
        <% include KBFeaturedArticles %>
        <% include KBLatestArticles %>
        <% include KBBestArticles %>
        <% include KBArticles %>
        <% if PageComments %>
            <div class="Form">$PageComments</div>
        <% end_if %>
    </div>
</div>