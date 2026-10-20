<div id="course-certificate-feedback-{{ $tableId }}" data-csrf-token="{{ csrf_token() }}" role="status" aria-live="polite">
	@if(session('success'))
		<div class="uk-alert-success" uk-alert>{{ session('success') }}</div>
	@endif
	@if($errors->any())
		<div class="uk-alert-danger" uk-alert>
			<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
		</div>
	@endif
</div>
@once
	@include('courses::operatorCourseSessions.certificateActions')
@endonce
