{{ $name->displayName }}様

パスワードの再設定を受け付けました。
下記 URL へアクセスし、新しいパスワードを設定してください。

{{ $url }}

・この URL の有効期限は {{ $expiredAt }} です。
　有効期限を過ぎてしまった場合は、お手数ですが再度最初からお手続きください。

@include("emails.footer")
