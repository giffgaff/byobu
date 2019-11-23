Hey {{ $user->username }},

{{ $blueprint->discussion->user->username }} started a new private discussion with you as recipient, titled '{{ $blueprint->discussion->title }}''.

View it here: {{ app()->url() }}/d/{{ $blueprint->discussion->id }}-{{ $blueprint->discussion->slug }}

Thanks,

The giffgaff community team


------

Please note: This email has been sent from an unmonitored account. Please do not reply directly to this email.
