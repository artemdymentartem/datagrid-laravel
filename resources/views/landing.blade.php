@extends('layouts.simple')

@section('content')
    <!-- Hero -->
    <div class="bg-image" style="background-image: url('{{ asset('media/photos/photo15@2x.jpg') }}');">
        <div class="hero bg-white overflow-hidden">
            <div class="hero-inner">
                <div class="content content-full text-center">
                    <h1 class="display-4 font-w700 mb-2">
                        Please <span class="text-primary">LOOK</span> <span class="font-w300"> below words carefully!</span>
                    </h1>
                    <h2 class="h3 font-w300 text-muted mb-5 invisible" data-toggle="appear" data-timeout="150">
                        Hi, i hope to work with you directly.<br>
                        I think you already sent your whatsapp to me, But i can't find you on whatsapp<br>
                        So if possible, i hope you contac to me via skype.<br>

                        this is my skype ID: <br>
                        live:.cid.4e68b49ea6426967<br>
                        If you are okay, you can contact to me via here.<br>

                        But remember one, don't mention about skype, whatsapp on website anymore<br>
                        It is out of rule and terms in freelancer<br>
                        If we work directly, and they found it on freelancer chat, <br>
                        they will close our account, or our accounts should be in danger<br>
                        Because of you also a backend developer, i think you can understand my words.<br>
                    </h2>
                    <span class="m-2 d-inline-block invisible" data-toggle="appear" data-timeout="300">
                        <a class="btn btn-hero-success" href="/dashboard">
                            <i class="fa fa-fw fa-sign-in-alt mr-1"></i> Enter Dashboard
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <!-- END Hero -->
@endsection
