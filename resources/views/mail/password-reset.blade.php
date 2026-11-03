<x-mail::message>
<h2>Restablece tu contraseña</h2>

<p class="greeting">Hola, {{ $userName }}.</p>

<p>Recibimos una solicitud para restablecer la contraseña de tu cuenta de SIGLA. Haz clic en el botón para crear una contraseña nueva.</p>

<x-mail::panel :rows="$details" />

<x-mail::button :url="$url">
Restablecer contraseña
</x-mail::button>

<p class="note">Si no hiciste esta solicitud, ignora este correo. Tu contraseña seguirá igual.</p>
</x-mail::message>
