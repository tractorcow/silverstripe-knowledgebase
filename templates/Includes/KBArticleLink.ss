<% require themedCSS(KBArticleRating) %>

<li class="Item">
    <a class="ItemTitle" href="$Link.ATT">$MenuTitle.XML</a>
    <% if RatingEnabled %>
        <div class="StaticRating Rating_$Rating" title="Rated $Rating/10">$Rating/10</div>
    <% end_if %>
</li>