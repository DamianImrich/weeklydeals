{extends file='layouts/layout-both-columns.tpl'}

{block name='left_column'}{/block}
{block name='right_column'}{/block}

{block name='content_wrapper'}
<div id="content-wrapper" class="full-width">
    {block name='content'}
        {hook h="displayWeeklyDealBlock"}
    {/block}
</div>
{/block}