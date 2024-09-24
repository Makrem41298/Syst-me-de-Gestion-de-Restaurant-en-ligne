@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://img.freepik.com/vecteurs-libre/restaurant-logo-modele_1236-155.jpg?t=st=1723823003~exp=1723826603~hmac=4575a1132bc73ab8f939f993ba63b1cb6814475ad493fed80877eba07b8a2ac1&w=740" class="logo" alt="Laravel Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
