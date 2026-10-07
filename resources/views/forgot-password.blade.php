<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Telkom Fallout System — Lupa Password</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --maroon-0:#3A0410;
    --maroon-1:#5C0A1B;
    --maroon-2:#8A0F26;
    --red:#C8102E;
    --gold:#D9A441;
    --paper:#FBF9F7;
    --ink:#20161A;
    --ink-lo:#7A6B6F;
    --line:#E7DEDD;
  }
  *{box-sizing:border-box; margin:0; padding:0;}
  html,body{
    height:100%;
    min-width:0;
    width:100%;
    max-width:100%;
  }
  body{
    font-family:'Inter', sans-serif;
    display:grid;
    grid-template-columns:56% 44%;
    min-height:100vh;
    overflow-x:hidden;
  }

  .brand-panel{
    position:relative;
    color:var(--paper);
    padding:64px 72px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    overflow:hidden;
    isolation:isolate;
  }
  .brand-photo{
    position:absolute;
    inset:0;
    background-image:url('{{ asset("images/gedung-telkom.jpg") }}');
    background-size:cover;
    background-position:65% 35%;
    z-index:-2;
  }
  .brand-overlay{
    position:absolute;
    inset:0;
    background:
      linear-gradient(180deg, rgba(58,4,16,0.55) 0%, rgba(58,4,16,0.82) 60%, var(--maroon-0) 100%),
      radial-gradient(120% 100% at 100% 0%, rgba(138,15,38,0.55), transparent 65%);
    z-index:-1;
  }

  .wordmark{
    position:absolute;
    top:32px;
    left:32px;
    z-index:2;
  }

  .headline-block{
    position:relative;
    z-index:2;
    max-width:480px;
    text-align:center;
  }
  h1{
    font-family:'Space Grotesk', sans-serif;
    font-weight:600;
    font-size:36px;
    line-height:1.22;
    letter-spacing:-0.01em;
  }

  .action-panel{
    background:var(--paper);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:64px;
  }
  .form-card{
    width:100%;
    max-width:380px;
  }

  .back-link{
    display:flex;
    align-items:center;
    gap:6px;
    font-size:13px;
    color:var(--ink-lo);
    text-decoration:none;
    margin-bottom:28px;
  }
  .back-link:hover{ color:var(--ink); }

  .icon-badge{
    width:44px; height:44px;
    border-radius:12px;
    background:rgba(200,16,46,0.08);
    display:grid;
    place-items:center;
    margin-bottom:20px;
  }

  .form-card h2{
    font-family:'Space Grotesk', sans-serif;
    font-size:22px;
    font-weight:600;
    color:var(--ink);
  }
  .form-card p.intro{
    margin-top:8px;
    font-size:13.5px;
    color:var(--ink-lo);
    line-height:1.6;
  }

  form{
    margin-top:24px;
    display:flex;
    flex-direction:column;
    gap:18px;
  }

  .field label{
    display:block;
    font-size:13px;
    font-weight:600;
    color:var(--ink);
    margin-bottom:7px;
  }
  .field-input{
    display:flex;
    align-items:center;
    gap:10px;
    border:1.4px solid var(--line);
    border-radius:10px;
    padding:12px 14px;
    background:#fff;
    transition:border-color 0.15s ease, box-shadow 0.15s ease;
  }
  .field-input:focus-within{
    border-color:var(--red);
    box-shadow:0 0 0 3px rgba(200,16,46,0.10);
  }
  .field-input input{
    border:none;
    outline:none;
    flex:1;
    min-width:0;
    font-family:'Inter', sans-serif;
    font-size:14px;
    color:var(--ink);
    background:transparent;
  }
  .field-input input::placeholder{ color:#B9AEAF; }
  .field-input svg{ flex:none; }

  .submit-btn{
    margin-top:4px;
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:var(--red);
    color:var(--paper);
    font-family:'Inter', sans-serif;
    font-size:14.5px;
    font-weight:600;
    cursor:pointer;
    transition:transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
  }
  .submit-btn:hover{
    background:#AD0E28;
    box-shadow:0 10px 24px -10px rgba(200,16,46,0.55);
  }
  .submit-btn:active{ transform:scale(0.985); }

  .switch-line{
    margin-top:22px;
    text-align:center;
    font-size:13.5px;
    color:var(--ink-lo);
  }
  .switch-line a{
    color:var(--red);
    font-weight:600;
    text-decoration:none;
  }
  .switch-line a:hover{ text-decoration:underline; }

  .flash{
    margin-top:16px;
    padding:11px 12px;
    border-radius:10px;
    font-size:12.5px;
    line-height:1.5;
  }
  .flash-success{
    background:#E8F7EE;
    color:#176B3B;
    border:1px solid #CBEBD7;
  }
  .flash-error{
    background:#FCEAED;
    color:#8B1327;
    border:1px solid #F3CDD5;
  }

  @media (max-width:900px){
    body{display:flex; flex-direction:column; overflow-y:auto;}
    .brand-panel{
      width:100%;
      min-height:48vh;
      padding:82px 34px 66px;
      align-items:flex-start;
      justify-content:center;
    }
    .wordmark{top:22px; left:24px;}
    .wordmark img{max-width:180px;}
    .headline-block{width:100%; max-width:600px; text-align:left;}
    h1{font-size:clamp(32px,8vw,42px); line-height:1.08;}
    .action-panel{
      width:100%;
      min-height:52vh;
      padding:42px 30px 48px;
      align-items:flex-start;
    }
    .form-card{max-width:520px; margin:0 auto;}
  }

  @media (max-width:600px){
    .brand-panel{min-height:44vh; padding:78px 22px 58px;}
    .wordmark{top:18px; left:20px;}
    .wordmark img{height:48px; max-width:150px;}
    .headline-block{max-width:100%;}
    h1{font-size:clamp(29px,9.5vw,37px); line-height:1.08;}
    .action-panel{min-height:56vh; padding:30px 20px 40px;}
    .form-card{max-width:100%;}
    .back-link{font-size:12px; margin-bottom:22px;}
    .icon-badge{width:40px; height:40px; margin-bottom:16px;}
    .form-card h2{font-size:24px;}
    .form-card p.intro{font-size:12px; line-height:1.55;}
    form{margin-top:22px; gap:16px;}
    .field label{font-size:12px;}
    .field-input{min-height:46px; padding:11px 12px;}
    .field-input input{font-size:13px;}
    .submit-btn{min-height:46px; padding:13px; font-size:13.5px;}
    .switch-line{font-size:12px; line-height:1.5;}
  }
</style>
</head>
<body>

  <section class="brand-panel">
    <div class="brand-photo"></div>
    <div class="brand-overlay"></div>

    <div class="wordmark">
      <img src="{{ asset('images/logo-telkom-white.png') }}" alt="Telkom Indonesia" style="height:72px;">
    </div>

    <div class="headline-block">
      <h1>Rekap Data - Fallout Jakarta Southern</h1>
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
          <rect x="5" y="10" width="14" height="10" rx="2" stroke="#C8102E" stroke-width="1.7"/>
          <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="#C8102E" stroke-width="1.7"/>
        </svg>
      </div>

      <h2>Lupa Password?</h2>
      <p class="intro">Tenang, masukkan email kamu dan kami kirim link buat reset password.</p>

      @if (session('status'))
        <div class="flash flash-success">
          {{ session('status') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="flash flash-error">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="field">
          <label for="email">Email</label>
          <div class="field-input">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
              <path d="M4 6h16v12H4z" stroke="#C8102E" stroke-width="1.6"/>
              <path d="M4 7l8 6 8-6" stroke="#C8102E" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <input id="email" name="email" type="email" placeholder="nama@telkom.co.id" value="{{ old('email') }}" required autocomplete="email">
          </div>
        </div>

        <button class="submit-btn" type="submit">Kirim Link Reset</button>
      </form>

      <div class="switch-line">
        Ingat password kamu? <a href="{{ route('login') }}">Login di sini</a>
      </div>
    </div>
  </section>

</body>
</html>
