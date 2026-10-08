<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Telkom Fallout System — Reset Password</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --maroon-0:#3A0410;
    --red:#C8102E;
    --paper:#FBF9F7;
    --ink:#20161A;
    --ink-lo:#7A6B6F;
    --line:#E7DEDD;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{
    min-height:100vh;
    display:grid;
    grid-template-columns:56% 44%;
    font-family:'Inter',sans-serif;
    background:var(--paper);
    color:var(--ink);
  }
  .brand-panel{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:64px;
    color:#fff;
    overflow:hidden;
    isolation:isolate;
  }
  .brand-photo{
    position:absolute; inset:0;
    background-image:url('{{ asset("images/gedung-telkom.jpg") }}');
    background-size:cover;
    background-position:65% 35%;
    z-index:-2;
  }
  .brand-overlay{
    position:absolute; inset:0;
    background:
      linear-gradient(180deg,rgba(58,4,16,.55),rgba(58,4,16,.82) 60%,rgba(58,4,16,.98)),
      radial-gradient(120% 100% at 100% 0%,rgba(138,15,38,.55),transparent 65%);
    z-index:-1;
  }
  .wordmark{position:absolute;top:32px;left:32px}
  .headline-block{max-width:480px;text-align:center}
  h1{font-family:'Space Grotesk',sans-serif;font-size:36px;line-height:1.22;font-weight:600}
  .action-panel{
    display:flex;align-items:center;justify-content:center;padding:64px;background:var(--paper)
  }
  .form-card{width:100%;max-width:380px}
  .back-link{
    display:flex;align-items:center;gap:6px;margin-bottom:28px;
    font-size:13px;color:var(--ink-lo);text-decoration:none
  }
  .back-link:hover{color:var(--ink)}
  .icon-badge{
    width:44px;height:44px;border-radius:12px;background:rgba(200,16,46,.08);
    display:grid;place-items:center;margin-bottom:20px
  }
  h2{font-family:'Space Grotesk',sans-serif;font-size:22px;font-weight:600}
  .intro{margin-top:8px;font-size:13.5px;color:var(--ink-lo);line-height:1.6}
  form{margin-top:24px;display:flex;flex-direction:column;gap:18px}
  .field label{display:block;font-size:13px;font-weight:600;margin-bottom:7px}
  .field-input{
    display:flex;align-items:center;gap:10px;border:1.4px solid var(--line);
    border-radius:10px;padding:12px 14px;background:#fff
  }
  .field-input:focus-within{border-color:var(--red);box-shadow:0 0 0 3px rgba(200,16,46,.10)}
  .field-input input{
    border:none;outline:none;flex:1;min-width:0;font:14px 'Inter',sans-serif;color:var(--ink);background:transparent
  }
  .submit-btn{
    width:100%;padding:14px;border:none;border-radius:10px;background:var(--red);color:#fff;
    font:600 14.5px 'Inter',sans-serif;cursor:pointer
  }
  .submit-btn:hover{background:#AD0E28}
  .flash{
    margin-top:16px;padding:11px 12px;border-radius:10px;font-size:12.5px;line-height:1.5;
    background:#FCEAED;color:#8B1327;border:1px solid #F3CDD5
  }
  .errors{margin-top:10px;color:#8B1327;font-size:12px;line-height:1.5}
  @media(max-width:900px){
    body{display:flex;flex-direction:column}
    .brand-panel{width:100%;min-height:42vh;padding:82px 34px 50px;justify-content:center}
    .wordmark{top:22px;left:24px}
    .headline-block{width:100%;text-align:left}
    h1{font-size:clamp(32px,8vw,42px);line-height:1.08}
    .action-panel{width:100%;padding:38px 30px 48px;align-items:flex-start}
    .form-card{max-width:520px;margin:0 auto}
  }
  @media(max-width:600px){
    .brand-panel{min-height:38vh;padding:76px 22px 48px}
    .wordmark{top:18px;left:20px}
    .wordmark img{height:48px;max-width:150px}
    .action-panel{padding:30px 20px 40px}
    h2{font-size:24px}
    .intro{font-size:12px}
    .field-input{min-height:46px;padding:11px 12px}
    .field-input input{font-size:13px}
    .submit-btn{min-height:46px;padding:13px;font-size:13.5px}
  }
</style>
<script src="{{ asset('js/tf-navigation.js') }}" defer></script>

</head>
<body data-tf-nav="public">

<section class="brand-panel">
  <div class="brand-photo"></div>
  <div class="brand-overlay"></div>
  <div class="wordmark">
    <img src="{{ asset('images/logo-telkom-white.png') }}" alt="Telkom Indonesia" style="height:72px;">
  </div>
  <div class="headline-block">
    <h1>Buat Password Baru</h1>
  </div>
</section>

<section class="action-panel">
  <div class="form-card">
    <a class="back-link" href="{{ route('login') }}">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
        <path d="M15 6l-6 6 6 6" stroke="#7A6B6F" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Kembali ke Login
    </a>

    <div class="icon-badge">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <path d="M7 10V7a5 5 0 0 1 10 0v3" stroke="#C8102E" stroke-width="1.7" stroke-linecap="round"/>
        <rect x="5" y="10" width="14" height="10" rx="2" stroke="#C8102E" stroke-width="1.7"/>
        <circle cx="12" cy="15" r="1.2" fill="#C8102E"/>
      </svg>
    </div>

    <h2>Reset Password</h2>
    <p class="intro">Masukkan email dan password baru untuk akun kamu.</p>

    @if ($errors->any())
      <div class="flash">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">

      <div class="field">
        <label for="email">Email</label>
        <div class="field-input">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
            <path d="M4 6h16v12H4z" stroke="#C8102E" stroke-width="1.6"/>
            <path d="M4 7l8 6 8-6" stroke="#C8102E" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
          <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email">
        </div>
      </div>

      <div class="field">
        <label for="password">Password Baru</label>
        <div class="field-input">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
            <rect x="5" y="10" width="14" height="10" rx="2" stroke="#C8102E" stroke-width="1.6"/>
            <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="#C8102E" stroke-width="1.6"/>
          </svg>
          <input id="password" name="password" type="password" required autocomplete="new-password">
        </div>
      </div>

      <div class="field">
        <label for="password_confirmation">Konfirmasi Password</label>
        <div class="field-input">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
            <rect x="5" y="10" width="14" height="10" rx="2" stroke="#C8102E" stroke-width="1.6"/>
            <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="#C8102E" stroke-width="1.6"/>
          </svg>
          <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
        </div>
      </div>

      <button class="submit-btn" type="submit">Simpan Password Baru</button>
    </form>
  </div>
</section>

</body>
</html>
