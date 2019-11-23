Hey {{ $user->username }},

{{ $blueprint->post->user->username }} replied in a private discussion with you as recipient, titled '{{ $blueprint->post->discussion->title }}''.

View it here: {{ app()->url() }}/d/{{ $blueprint->post->discussion->id }}-{{ $blueprint->post->discussion->slug }}/{{ $blueprint->post->number }}

Thanks,

The giffgaff community team


---

{!! $blueprint->post->content !!}

------

Please note: This email has been sent from an unmonitored account. Please do not reply directly to this email.
