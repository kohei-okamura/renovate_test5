{{ $staff->name->displayName }} 様

下記の勤務シフトが《変更》されました。
ご不明な点などがございましたら担当者までご連絡ください。

【日時】
変更前：{{ $originalSchedule }}
変更後：{{ $updatedSchedule }}

【利用者名】
変更前：{{ $originalUserName }}
変更後：{{ $updatedUserName }}

@if ($note)
    【備考】
    {{ $note }}
@endif

@include("emails.footer")
