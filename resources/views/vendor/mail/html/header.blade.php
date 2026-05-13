@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo">
@else
<div style="display: flex; align-items: center; gap: 10px;">
    <img src="https://mundoasiatico.cl/logo-mundo-asiatico.webp" 
         style="max-height: 50px; width: auto;" 
         alt=""
         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
    <span style="color: #dc2626; font-size: 24px; font-weight: bold; font-family: 'Outfit', sans-serif; display: none;">
        Mundo Asiático
    </span>
</div>
@endif
</a>
</td>
</tr>
