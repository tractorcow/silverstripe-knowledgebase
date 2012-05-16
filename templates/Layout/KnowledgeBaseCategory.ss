<div class="KnowledgeBaseArticle KnowledgeBase">
    <div id="Sidebar">
        <% include KBQuickSearch %>
        <% include KBCategoryMenu %>
    </div>
    <div id="Content">
        <% include BreadCrumbs %>
        <h1>$Title.XML</h1>
        <% if Description %>
            <p class="Description">$Description</p>
        <% end_if %>
        <% if Content %>
            <div class="Copy typography">
                $Content
            </div>
        <% end_if %>
        <% include KBCategories %>
        <% include KBSubArticles %>
        <% if PageComments %>
            <div class="Form">$PageComments</div>
        <% end_if %>
    </div>
</div>