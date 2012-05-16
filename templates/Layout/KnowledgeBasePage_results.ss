<div class="KnowledgeBase">
    <div id="Sidebar">
        <% include KBQuickSearch %>
    </div>
    <div id="Content">
        <% include BreadCrumbs %>
        <h1>$Title.XML</h1>

        <% if Results %>
            <ul id="SearchResults">
                <% control Results %>
                <li>
                    <% if MenuTitle %>
                        <h3><a class="searchResultHeader" href="$Link">$MenuTitle</a></h3>
                    <% else %>
                        <h3><a class="searchResultHeader" href="$Link">$Title</a></h3>
                    <% end_if %>
                    <% if Content %>
                        $Content.FirstParagraph(html)
                    <% end_if %>
                    <a class="readMoreLink" href="$Link" title="<% sprintf(_t('READ_MORE_ABOUT','Read more about &quot;%s&quot;'),$Title.ATT) %>"><% sprintf(_t('READ_MORE_ABOUT','Read more about &quot;%s&quot;'),$Title.XML) %>...</a>
                </li>
                <% end_control %>
            </ul>
        <% else %>
            <p><% _t('SEARCH_NO_RESULTS', 'Sorry, your search query did not return any results.') %></p>
        <% end_if %>

        <% if Results.MoreThanOnePage %>
            <div id="PageNumbers">
                <% if Results.NotLastPage %>
                    <a class="next" href="$Results.NextLink" title="<% _t('NEXT', 'Next') %>"><% _t('NEXT', 'Next') %></a>
                <% end_if %>
                <% if Results.NotFirstPage %>
                    <a class="prev" href="$Results.PrevLink" title="<% _t('PREV', 'Prev') %>"><% _t('PREV', 'Prev') %></a>
                <% end_if %>
                <span>
                <% control Results.SummaryPagination(5) %>
                    <% if CurrentBool %>
                        $PageNum
                    <% else %>
                        <a href="$Link" title="$PageNum">$PageNum</a>
                    <% end_if %>
                <% end_control %>
                </span>

            </div>
        <% end_if %>
        
    </div>
</div>