import QRCode from 'qrcode';

/**
 * Identité locale sans compte : un token aléatoire + un pseudo, stockés
 * dans le localStorage de l'appareil, pour signer et retrouver ses notes.
 */
window.LeMur = {
    token() {
        let token = localStorage.getItem('lemur_token');

        if (!token) {
            token = [...crypto.getRandomValues(new Uint8Array(20))]
                .map((byte) => byte.toString(16).padStart(2, '0'))
                .join('');
            localStorage.setItem('lemur_token', token);
        }

        // Miroir en cookie pour que le serveur reconnaisse "mes" notes dès le premier rendu.
        document.cookie = `lemur_token=${token}; path=/; max-age=31536000; samesite=lax`;

        return token;
    },

    pseudo() {
        return localStorage.getItem('lemur_pseudo') || '';
    },

    savePseudo(pseudo) {
        localStorage.setItem('lemur_pseudo', (pseudo || '').trim());
    },

    color() {
        return localStorage.getItem('lemur_color') || 'jaune';
    },

    saveColor(color) {
        localStorage.setItem('lemur_color', color);
    },

    // Réactions déjà posées par cet appareil : { [noteId]: ['😂', ...] }
    reactedEmojis(noteId) {
        const all = JSON.parse(localStorage.getItem('lemur_reactions') || '{}');

        return all[noteId] || [];
    },

    hasReacted(noteId, emoji) {
        return this.reactedEmojis(noteId).includes(emoji);
    },

    rememberReaction(noteId, emoji, reacted) {
        const all = JSON.parse(localStorage.getItem('lemur_reactions') || '{}');
        const list = new Set(all[noteId] || []);

        reacted ? list.add(emoji) : list.delete(emoji);
        all[noteId] = [...list];
        localStorage.setItem('lemur_reactions', JSON.stringify(all));
    },

    async qr(element, text) {
        element.innerHTML = await QRCode.toString(text, {
            type: 'svg',
            margin: 1,
            color: { dark: '#201e1a', light: '#ffffff' },
        });
        element.firstElementChild.removeAttribute('width');
        element.firstElementChild.removeAttribute('height');
    },

    async copy(text) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch {
            const input = document.createElement('textarea');
            input.value = text;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            input.remove();

            return true;
        }
    },
};

// Pose le cookie d'identité dès le chargement de la page.
window.LeMur.token();
