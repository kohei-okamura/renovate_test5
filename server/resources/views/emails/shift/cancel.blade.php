{{ $staff->name->displayName }} 様

下記の勤務シフトが《キャンセル》されました。
ご不明な点などがございましたら担当者までご連絡ください。

【日時】
{{ $schedule }}

@if ($userName)
【利用者名】
{{ $userName }}
@endif

@if ($note)
【備考】
{{ $note }}
@endif

@include("emails.footer")
