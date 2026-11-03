@props(['url'])
<table class="block" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="block-cell">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="button-cell" align="center" bgcolor="#002f7f" height="48">
<a href="{{ $url }}" class="button" target="_blank" rel="noopener">{!! $slot !!}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
