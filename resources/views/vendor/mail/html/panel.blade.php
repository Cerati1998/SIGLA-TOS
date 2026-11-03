@props(['rows'])
<table class="block" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="block-cell">
<table class="panel" width="100%" cellpadding="0" cellspacing="0" role="presentation" bgcolor="#f3f5f9">
<tr>
<td class="panel-cell">
<table class="panel-rows" width="100%" cellpadding="0" cellspacing="0" role="presentation">
@foreach ($rows as $label => $value)
<tr>
<td class="panel-label">{{ $label }}</td>
<td class="panel-value" align="right">{{ $value }}</td>
</tr>
@unless ($loop->last)
<tr>
<td class="panel-gap" colspan="2">&nbsp;</td>
</tr>
@endunless
@endforeach
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
