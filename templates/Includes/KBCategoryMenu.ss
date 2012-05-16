<div class="sidebarBox">
    <h3><% _t('CATEGORIES', 'Categories') %></h3>

    <ul class="CategoryMenu">
        <% control BaseCategories %>
            <li class="$LinkingMode">
                <a href="$Link" class="$LinkingMode">$MenuTitle.XML <% if SubArticles %>($SubArticles.Count)<% end_if %></a>

                <% if Categories %>
                    <% if LinkOrSection = section %>
                        <ul>
                            <% control Categories %>
                                <li><a href="$Link" class="$LinkingMode">$MenuTitle.XML</a></li>
                            <% end_control %>
                        </ul>
                    <% end_if %>
                <% end_if %>
            </li>
        <% end_control %>
    </ul>
</div>