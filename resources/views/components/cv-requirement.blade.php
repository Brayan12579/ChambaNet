@props(['job'])
<span @class(['badge', 'badge-cv-required' => $job->cv_required, 'badge-cv-optional' => !$job->cv_required])><x-icon :name="$job->cv_required ? 'file' : 'check'" :size="15" />{{ $job->cv_required ? 'CV obligatorio' : 'CV opcional' }}</span>
