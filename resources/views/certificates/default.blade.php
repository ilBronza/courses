<!DOCTYPE html>
<html lang="it">
<head>
	<meta charset="utf-8">
	<title>{{ $header['title'] ?? 'Attestato' }}</title>
	<style>
		@page { margin: 8mm; }
		body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #161616; }
		.certificate { border: 1.5pt solid #202020; min-height: 170mm; padding: 5mm 7mm; }
		table { width: 100%; border-collapse: collapse; table-layout: fixed; }
		td { text-align: center; vertical-align: top; }
		.organizations td { vertical-align: middle; padding: 0 3mm; }
		.organizations img { max-width: 65mm; max-height: 14mm; }
		.organization-name { font-size: 9pt; }
		h1 { font-size: 19pt; font-weight: normal; text-align: center; margin: 3mm 0 1mm; }
		.subtitle { font-size: 7.5pt; text-align: center; margin: 0 0 1mm; }
		.participant { text-align: center; margin-top: 3mm; }
		.participant p { margin: 0.6mm 0; }
		.participant-name { font-size: 20pt; font-weight: bold; margin: 1.5mm 0 !important; }
		.participant-detail { font-size: 9pt; }
		.course { text-align: center; margin-top: 3mm; }
		.course p { margin: 0.8mm 0; }
		h2 { color: #d82525; font-size: 17pt; line-height: 1.12; margin: 1.5mm 0 2mm; }
		.course-details { font-size: 9pt; line-height: 1.25; }
		.disclaimer { font-size: 7.5pt; text-align: center; margin-top: 2mm; }
		.disclaimer p { margin: 0.7mm 0; }
		.signatures { margin-top: 4mm; page-break-inside: avoid; }
		.signatures td { padding: 0 2mm 1mm; font-size: 7.5pt; }
		.signatures p { margin: 0.7mm 0; }
		.signature-role { font-style: italic; }
		.signature-image { height: 13mm; margin-top: 1mm; }
		.signature-image img { max-width: 43mm; max-height: 13mm; }
		.issue { text-align: right; font-size: 7.5pt; margin-top: 2mm; }
	</style>
</head>
<body>
@php
	$formatDate = static fn ($date) => \Carbon\CarbonImmutable::createFromFormat('!Y-m-d', $date)->format('d/m/Y');
@endphp
<div class="certificate">
	@if(count($header['organizations']))
		<table class="organizations"><tr>
			@foreach($header['organizations'] as $organization)
				<td style="width: {{ 100 / count($header['organizations']) }}%">
					@if($organization['logo'] ?? null)
						<img src="{{ $organization['logo'] }}" alt="{{ $organization['name'] }}">
					@else
						<div class="organization-name">{{ $organization['name'] }}</div>
					@endif
				</td>
			@endforeach
		</tr></table>
	@endif
	@if($header['title'] ?? null)<h1>{{ $header['title'] }}</h1>@endif
	@if($header['subtitle'] ?? null)<p class="subtitle">{{ $header['subtitle'] }}</p>@endif

	<div class="participant">
		<p>Si attesta che</p>
		<p class="participant-name">{{ $participant['name'] }}</p>
		@if($participant['tax_code'] ?? null)<p>{{ $participant['tax_code'] }}</p>@endif
		@if($participant['company'] ?? null)<p class="participant-detail">per conto di <strong>{{ $participant['company'] }}</strong></p>@endif
		@if($participant['role'] ?? null)<p class="participant-detail">in qualità di <strong>{{ $participant['role'] }}</strong></p>@endif
		@if($participant['ateco'] ?? null)<p class="participant-detail">nel settore d'impresa individuato dal codice ATECO {{ $participant['ateco'] }}</p>@endif
	</div>

	<div class="course">
		<p>ha frequentato @if($course['delivery_mode'] ?? null)in modalità {{ $course['delivery_mode'] }} @endif il corso</p>
		<h2>{{ $course['title'] }}</h2>
		@if($course['classification'] ?? null)<p>{{ $course['classification'] }}</p>@endif
		<p class="course-details">
			della durata di {{ rtrim(rtrim(number_format((float) $course['hours'], 2, ',', ''), '0'), ',') }} ore,
			@if(($course['starts_on'] ?? null) && $course['starts_on'] !== $course['ends_on'])
				svoltosi dal {{ $formatDate($course['starts_on']) }} al {{ $formatDate($course['ends_on']) }}
			@else
				svoltosi il {{ $formatDate($course['ends_on']) }}
			@endif
			@if($course['location'] ?? null)<br>presso {{ $course['location'] }}@endif
		</p>
		@if(count($course['dates']))
			<p class="course-details">
				@foreach($course['dates'] as $date)
					{{ $date['label'] }}: {{ $formatDate($date['date']) }}@if(! $loop->last) · @endif
				@endforeach
			</p>
		@endif
	</div>

	<div class="disclaimer">
		@if($disclaimer['assessment'] ?? null)<p>{{ $disclaimer['assessment'] }}</p>@endif
		@foreach($disclaimer['paragraphs'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach
		@foreach($disclaimer['notes'] as $note)<p>{{ $note }}</p>@endforeach
	</div>

	@if(count($signatures))
		<table class="signatures">
			@foreach(array_chunk($signatures, 4) as $row)
				<tr>
					@foreach($row as $signature)
						<td style="width: {{ 100 / count($row) }}%">
							@if($signature['role'] ?? null)<p class="signature-role">{{ $signature['role'] }}</p>@endif
							<p>{{ $signature['name'] }}</p>
							@if($signature['organization'] ?? null)<p>{{ $signature['organization'] }}</p>@endif
							@if($signature['image'] ?? null)<div class="signature-image"><img src="{{ $signature['image'] }}" alt="Firma di {{ $signature['name'] }}"></div>@endif
						</td>
					@endforeach
				</tr>
			@endforeach
		</table>
	@endif
	@if(($disclaimer['place'] ?? null) || ($disclaimer['issued_on'] ?? null))
		<div class="issue">{{ $disclaimer['place'] ?? '' }}@if(($disclaimer['place'] ?? null) && ($disclaimer['issued_on'] ?? null)), @endif{{ ($disclaimer['issued_on'] ?? null) ? $formatDate($disclaimer['issued_on']) : '' }}</div>
	@endif
</div>
</body>
</html>
