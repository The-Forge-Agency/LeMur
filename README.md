<div align="center">

# 📌 LeMur

### Crée un mur, partage le lien. Tout le monde y colle ses notes.

Un tableau de post-it partagé que tu crées en un clic : les punchlines du groupe,
la liste de la coloc, les adresses du voyage, les messages d'un anniv —
sans compte, sans installation, sans friction.

**Zéro compte · lien partageable · open source · gratuit**

Projet **#11/52** — [The Forge Agency](https://buymeacoffee.com/tfa.the.forge.agency)

</div>

---

## Le problème

Dès qu'un groupe veut garder des trucs ensemble, ça finit éparpillé : noyé dans
une conv WhatsApp qui scrolle, dans un Google Doc que personne ne rouvre, dans
les notes perso d'une seule personne. Et les vrais outils partagés (Notion,
Trello…) demandent à chacun de **créer un compte** — trop de friction pour
partager trois notes.

## La solution

LeMur, c'est un mur de post-it partagé. Tu nommes ton mur, tu obtiens un lien,
tu l'envoies à ta bande : tout le monde peut coller ses notes, réagir en emoji
et tout retrouver au même endroit. Le lien donne accès, un code PIN optionnel
verrouille l'écriture, et le créateur garde un **lien admin secret** pour tout
modérer. Zéro compte, zéro email, aucune donnée perso.

## ✨ Fonctionnalités

- 🧱 **Création en un clic** — un nom, un lien partageable avec un id non
  devinable (ULID), c'est tout. Le créateur reçoit en plus un lien admin secret.
- 📝 **Coller une note en deux secondes** — texte, pseudo optionnel et couleur
  de post-it, mémorisés en local (`localStorage`) pour signer sans compte.
- 🎨 **Façon tableau de liège** — post-it colorés légèrement inclinés, écriture
  manuscrite (Caveat), la plus récente en haut, épinglées en tête. Mobile-first.
- 😂 **Réactions emoji** — 😂 🔥 👍 ❤️ sur chaque note, auteur et date relative
  (« il y a 2 h »). Le fun social, sans compte.
- 🏷️ **Organisation légère** — hashtags libres (#Courses, #Voyage, #BestOf),
  filtre par tag, recherche texte, épinglage en haut du mur.
- ✍️ **Édition maîtrisée** — chacun modifie/supprime **ses** notes (repérées par
  un token local), l'admin peut tout modérer. Pas de bazar.
- 🔒 **Contrôle d'accès simple** — mur privé par son lien, PIN optionnel pour
  verrouiller l'écriture, mode lecture seule pour partager un best-of figé.
- 🔗 **Partage en un clic** — copier le lien, QR code généré côté client
  (parfait en soirée), aperçu Open Graph propre dans WhatsApp/Slack/Discord.
- 🔄 **Quasi-live** — polling Livewire léger (`wire:poll`), les notes des autres
  apparaissent sans recharger. (Le vrai temps réel WebSocket viendra plus tard.)
- 🙅 **Zéro compte, jamais de paywall** — aucune inscription, aucune donnée
  perso, 100% gratuit, financé par les dons.

## 🖼️ Écrans

| Landing + création | Le mur | Espace admin |
|:---:|:---:|:---:|
| `/` | `/m/{id}` | `/m/{id}/gerer?k={token}` |

## 🔐 Modèle d'accès (sans compte)

```
CRÉATION
  nom du mur ──▶ le serveur génère { public_id (ULID), admin_token (secret) }
                        │
        lien partage  /m/{public_id}          ──▶ pour la bande
        lien admin    /m/{public_id}/gerer?k= ──▶ pour le créateur uniquement

CONTRIBUTION (qui a le lien)
  colle une note ──▶ signée par un token aléatoire local (localStorage + cookie)
                     ──▶ chacun ne peut modifier/supprimer QUE ses notes
  admin (session via lien admin) ──▶ peut tout épingler / modifier / supprimer

VERROUS OPTIONNELS
  PIN (haché bcrypt) ──▶ il faut le code pour écrire, la lecture reste libre
  lecture seule      ──▶ seul l'admin peut modifier le mur
```

**Ce que le serveur stocke :** le nom du mur, les notes (texte, pseudo choisi,
couleur, tags, réactions), des tokens aléatoires. **Jamais :** email, compte,
mot de passe, IP, donnée perso.

## 🧱 Stack

Laravel 13 · PHP 8.3+ · Livewire 4 (composants single-file) · Alpine.js ·
Tailwind CSS v4 (design tokens) · SQLite / MySQL · Vite · fonts Bunny
(Bricolage Grotesque, Inter, Caveat) · QR code 100% client (`qrcode`) ·
Pest 4 · déploiement Laravel Forge.

## 🚀 Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
php artisan serve            # http://localhost:8000
```

En développement, `composer run dev` lance serveur + queue + logs + Vite en
parallèle. `php artisan db:seed` crée un mur de démo (le lien s'affiche dans la
console).

## ☁️ Déploiement (Laravel Forge)

1. Connecte le dépôt `The-Forge-Agency/LeMur`, branche `main`.
2. Deploy script :
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   npm ci && npm run build
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
3. Renseigne `APP_URL` (les liens de partage en dépendent) et les variables
   ci-dessous.

## ⚙️ Configuration

Tout est piloté par variables d'environnement — le même codebase tourne hébergé
ou self-hosté, sans toucher au code.

| Variable | Rôle | Défaut |
|----------|------|--------|
| `APP_URL` | Base des liens de partage et QR codes | `http://localhost` |
| `DB_CONNECTION` | `sqlite` (self-host) ou `mysql` (hébergé) | `sqlite` |
| `LEMUR_COFFEE_URL` | Lien « Buy me a coffee » dans l'UI | page de dons |
| `LEMUR_GITHUB_URL` | Lien « Code source » dans le footer | dépôt public |

## 🗂️ Structure

```
app/
  Http/Controllers/WallController.php    # show (mur) / manage (admin via token)
  Models/Wall.php                        # public_id ULID, admin_token, PIN, lecture seule
  Models/Note.php                        # contenu, tags extraits, réactions, ownership
resources/
  views/components/⚡create-wall.blade.php    # création du mur (landing)
  views/components/⚡wall-board.blade.php     # le mur : notes, réactions, filtres, PIN, partage
  views/components/⚡wall-settings.blade.php  # réglages admin : nom, PIN, lecture seule
  views/home.blade.php                   # landing « on vend par l'exemple »
  views/walls/{show,manage}.blade.php    # pages mur + admin (OG tags, noindex)
  js/app.js                              # identité locale (token/pseudo), réactions, QR, copie
  css/app.css                            # design tokens (palette, fonts, ombres)
routes/web.php                           # / · /m/{id} · /m/{id}/gerer
tests/                                   # 41 tests Pest (création, notes, PIN, admin, réactions…)
```

## 🧪 Tests

```bash
php artisan test           # 41 tests : ownership, PIN, lecture seule, modération, réactions
composer test              # pint + phpstan + pest
```

## 🤝 Contribuer & self-hosting

Le code est public par principe : pas de compte, pas de tracking, tu peux le
vérifier — et héberger ton propre mur si tu préfères. Fork, clone, audite, ou
propose une PR (une feature = une branche `dev/feature-xxx`).

## 💛 Licence & financement

Open source, **100% gratuit, jamais de plan payant**. Financé par des dons —
[Buy me a coffee](https://buymeacoffee.com/tfa.the.forge.agency).

---

<div align="center">
<sub>Un lien, un mur, tout le monde y colle ses notes. · Projet #11/52 · The Forge Agency</sub>
</div>
