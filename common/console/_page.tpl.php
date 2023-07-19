<?php
if($page && $page['total'] > 1)
{
    $page['show'] = 5;

    if (defined('IS_PJAX') && IS_PJAX)
        $page['url'] = str_replace(array('&_pjax=%23main', '?_pjax=%23main'), array('', '?'), $page['url']);

    echo '<li'.($page['now'] - ceil($page['show']/2) > 0 ? '' : ' class="disabled"')."><a data-pjax-container=\"#main\" href=\"{$page['url']}1\">&laquo;</a></li>";

    $start = $page['now'] - floor($page['show']/2) > 0 ? $page['now'] - floor($page['show']/2) : 1;

    $end = $page['now'] + floor($page['show']/2) > $page['total'] ? $page['total'] : $page['now'] + floor($page['show']/2);

    if ($end == $page['total'] && $page['total'] - $page['show'] + 1 > 0) $start = $page['total'] - $page['show'] + 1;
    if ($start == 1 && $start + $page['show'] - 1 <= $page['total']) $end = $start + $page['show'] - 1;

    for ($i = $start; $i <= $end; $i++)
    {
        $class = '';
        if ($i == $page['now']) $class .= ' active';
        if ($page['now'] < 2)
        {
            if ($i > $start + 2) $class .= ' hidden-xs';
        }
        else if ($page['now'] > $page['total'] - 2)
        {
            if ($i < $end - 2) $class .= ' hidden-xs';
        }
        else
        {
            if (!in_array($i, array($page['now'] + 1, $page['now'], $page['now'] - 1))) $class .= ' hidden-xs';
        }
        echo "<li class=\"{$class}\"><a data-pjax-container=\"#main\" href=\"{$page['url']}{$i}\">{$i}</a></li>";
    }

    echo '<li class="hidden-xs"><span><input title="'.__('Input page number, Enter to jump').'" value="" data-url="'.$page['url'].'" placeholder="Go" /></span></li>';

    echo '<li'.($page['now'] + ceil($page['show']/2) <= $page['total'] ? '' : ' class="disabled"')."><a data-pjax-container=\"#main\" href=\"{$page['url']}{$page['total']}\">&raquo;</a></li>";

}
