<div class="dashboard-panel">
    <h3><% _t('RecentCommentsDashboardPanel.PANELTITLE', 'Recent Comments') %></h3>
    <% if $Results %>
        <table class="table">
            <thead>
            <tr>
                <th><% _t('RecentCommentsDashboardPanel.COMMENT', 'Comment') %></th>
            </tr>
            </thead>
            <tbody>
                <% loop $Results %>
                <tr>
                    <td>
                        <div class="date" style="float: right; font-weight: italice">
                        $CreatedString
                        </div>
                        <div class="object"><b>
                        <% if $Object.CMSEditLink %>
                        <a href="$Object.CMSEditLink">
                            $Object.Title
                        </a>
                        <% else %>
                            $Object.Title
                        <% end_if %>
                        </b></div>
                        <p class="comment">$Comment</p>
                        <p class="author"><i>$Author.Name<br></i></p>
                    </td>
                </tr>
                <% end_loop %>
            </tbody>
        </table>
    <% else %>
        <p><% _t('RecentCommentsDashboardPanel.NO_COMMENTS', 'No recent comments.') %></p>
    <% end_if %>
</div>
