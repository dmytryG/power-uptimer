<?php
?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.copy-token').forEach(el => {
            el.addEventListener('click', () => {
                const text = el.innerText;

                // Современный способ
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(() => {
                        showCopied(el);
                    }).catch(() => {
                        fallbackCopy(text, el);
                    });
                } else {
                    fallbackCopy(text, el);
                }
            });
        });
    });

    function fallbackCopy(text, el) {
        const textarea = document.createElement('textarea');
        textarea.value = text;

        // чтобы не прыгал экран
        textarea.style.position = 'fixed';
        textarea.style.top = '-1000px';

        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();

        try {
            document.execCommand('copy');
            showCopied(el);
        } catch (e) {
            alert("Can't copy");
            console.error(e)
        }

        document.body.removeChild(textarea);
    }

    function showCopied(el) {
        const original = el.innerText;
        el.innerText = 'Copied ✔';
        el.classList.add('copied');

        setTimeout(() => {
            el.innerText = original;
            el.classList.remove('copied');
        }, 1000);
    }
</script>
