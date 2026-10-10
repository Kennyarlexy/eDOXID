<div class="card-header pb-2">
    <h4>{{ $submission->title }}</h4>
    <div class="d-flex flex-wrap">
        <span class="text-muted me-4 mb-2">
            <span
                class="badge {{ badge_class($submission->status) }} text-capitalize">{{ status_english_to_bahasa($submission->status) }}</span>
        </span>
        <span class="text-muted me-4 mb-2">
            <i class="fa fa-calendar me-1"></i>
            {{ $submission->created_at ? $submission->created_at->format('d M Y') : '-' }}
        </span>
        <span class="text-muted me-4 mb-2">
            <i class="fa fa-folder me-1"></i>
            {{ $submission->type->name }}
        </span>
        <span class="text-muted mb-2">
            <i class="fa-solid fa-box-archive me-1"></i>
            {{ $submission->skill_group_id ? $submission->skill_group->name : '-' }}
        </span>
    </div>
    <div class="mb-2 d-flex">
        <p class="text-muted">
        <div class="d-flex align-items-center">
            <div class="avatar">
                <a>
                    <img src="{{ $submission->student->profile_image == null || $submission->student->profile_image == '//spota.untan.ac.id/img/' ? asset('/img/user.png') : $submission->student->profile_image }}"
                        alt="{{ $submission->student->name }}">
                    <span class="ms-2">{{ $submission->student->name }} -
                        {{ $submission->student->unique_id }}</span>
                </a>
            </div>
        </div>
        </p>
    </div>
    <div class="d-flex mb-4 flex-wrap">
        <b class="me-2">Informasi lainnya:</b>
        <span class="me-2 badge bg-light-info">
            Jumlah SKS: {{ $submission->amount_sks_now ?: '-' }}
        </span>
        <span class="me-2 badge bg-light-info">
            IPK: {{ $submission->ipk_now ?: '-' }}
        </span>
    </div>
</div>
