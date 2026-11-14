# GitHub se cPanel par Auto-Deploy - ek baar ka setup

Isके baad: **`git push` karo → kuch seconds me live site update ho jaayegi**, koi manual cPanel click nahi.

Ye setup sirf EK BAAR karna hai (~10-15 minute). Iske baad sab automatic hai. Niche har step me bataya hai
kahan kya daalna hai.

---

## Step 0 - Code GitHub par push karo

Tumhare GitHub account ka access sirf tumhare paas hai, isliye ye command khud apne Terminal (ya VS Code) me chalao:

```bash
git remote add origin https://github.com/gulshanSharma11/sector7xnoida.git
git branch -M main
git push -u origin main
```

(Pehli baar push karte waqt browser me GitHub login maang sakta hai - normal hai.)

---

## Step 1 - cPanel me Git repo clone karo

1. cPanel kholo → search karo **"Git Version Control"**.
2. **Create** par click karo.
3. **Clone a Repository** ON rakho.
4. **Clone URL**: `https://github.com/gulshanSharma11/sector7xnoida.git`
5. **Repository Path**: `repositories/sector7xnoida` (public_html ke BAHAR - important, security ke liye)
6. **Repository Name**: `sector7xnoida` (yaad rakho, aage lagega)
7. **Create** dabao.

> Agar repo **private** rakha hai GitHub par, to clone karte waqt cPanel tumse GitHub username +
> Personal Access Token maangega (GitHub → Settings → Developer settings → Personal access tokens →
> "repo" scope wala token banao, wahi yahan paste karo).

---

## Step 2 - `.cpanel.yml` me apna real path daalo

Abhi `.cpanel.yml` file me `CPANELUSER` aur `public_html/sector7xnoida` placeholder hai. Isko apne
asli cPanel username aur site ke asli live folder se badlo:

1. cPanel File Manager me dekho site ka live folder kaunsa hai (jahan abhi `index.php`, `.htaccess` hai).
2. GitHub par `.cpanel.yml` file edit karo (ya locally edit karke `git push` kar do) - har line me
   `/home/CPANELUSER/public_html/sector7xnoida` ko apne real path se replace karo.

---

## Step 3 - Server par real `config.php` banao (ek baar)

Git me sirf `inc/config.sample.php` hai (safe template, real password nahi). Live site chalne ke liye
server par ek real `inc/config.php` chahiye - ye kabhi git se overwrite nahi hoga:

1. cPanel File Manager me apne live site folder ke `inc/` me jao.
2. `config.sample.php` ko copy karke naam do **`config.php`**.
3. Edit karo: real DB name/user/password (cPanel > MySQL Databases se), aur admin panel ka password.

---

## Step 4 - Deploy ek baar manually chala ke test karo

cPanel > Git Version Control > apne repo ke saamne **Manage** > **Pull or Deploy** tab:
1. **Update from Remote** dabao (GitHub se latest code repo path me le aayega).
2. **Deploy HEAD Commit** dabao (`.cpanel.yml` chalega, files live folder me copy honge).
3. Site check karo - sab sahi dikhna chahiye.

---

## Step 5 - cPanel API Token banao

1. cPanel me search karo **"Manage API Tokens"**.
2. **Create** → naam do `github-deploy` → **Create** dabao.
3. Jo token dikhega, usko turant copy kar lo (dobara nahi dikhega).

---

## Step 6 - GitHub me 4 secrets add karo

GitHub repo kholo → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**.
Ye 4 secrets banao:

| Secret name | Value |
|---|---|
| `CPANEL_HOST` | Tumhara cPanel hostname ya IP, bina `https://` ke (e.g. `68.178.174.53` ya `callastro.yourhost.com`) |
| `CPANEL_USERNAME` | Tumhara cPanel username |
| `CPANEL_API_TOKEN` | Step 5 wala token |
| `CPANEL_REPO_ROOT` | Poora path jo Step 1 me clone hua, e.g. `/home/CPANELUSER/repositories/sector7xnoida` |

---

## Bas, ho gaya! Ab kya hoga

Ab jab bhi tum (ya main) code change karke `git push origin main` karoge:
1. GitHub Actions automatically chalega (`.github/workflows/deploy-cpanel.yml`)
2. Wo cPanel ko bolega: GitHub se latest code pull karo
3. Phir bolega: deploy karo (`.cpanel.yml` ke tasks chalke live folder me files copy ho jaayengi)
4. 10-20 second me site live update ho jaayegi - koi cPanel login, koi click nahi chahiye.

GitHub repo ke **Actions** tab me har deploy ka status/log dikhega (green tick = sab sahi gaya).

`inc/config.php` (real passwords) kabhi touch nahi hota - wo hamesha server par hi rehta hai.
