/**
 * Calcula una puntuación de legibilidad aproximada usando la fórmula
 * Flesch-Szigriszt adaptada al español.
 *
 * Fórmula: 206.835 - (62.3 * síl/palabras) - (palabras/oraciones)
 * El resultado se normaliza a 0-100 para la barra de progreso.
 *
 * @param {string} htmlContent - Contenido HTML del artículo
 * @returns {number} Puntuación de 0 a 100
 */
export const calculateReadability = (htmlContent) => {
    if (!htmlContent) return 0;

    // 1. Limpiar HTML
    const text = htmlContent.replace(/<[^>]*>?/gm, '').replace(/&[a-z]+;/gi, ' ').trim();
    if (!text) return 0;

    // 2. Contar palabras
    const words = text.split(/\s+/).filter(w => w.length > 0);
    const wordCount = words.length;
    if (wordCount === 0) return 0;

    // 3. Contar oraciones (termina en . ! ?)
    const sentences = text.split(/[.!?]+/).filter(s => s.trim().length > 0);
    const sentenceCount = Math.max(sentences.length, 1);

    // 4. Contar sílabas (heurística para español)
    const countSyllables = (word) => {
        word = word.toLowerCase().replace(/[^a-záéíóúüñ]/gi, '');
        if (word.length <= 3) return 1;
        const vowels = word.match(/[aeiouáéíóúü]/gi) || [];
        let syllables = vowels.length;
        // Restar diptongos comunes
        const diphthongs = (word.match(/[aeiou][aeiou]/gi) || []).length;
        syllables = Math.max(1, syllables - Math.floor(diphthongs * 0.5));
        return syllables;
    };

    const totalSyllables = words.reduce((sum, w) => sum + countSyllables(w), 0);

    // 5. Fórmula Flesch-Szigriszt para español
    const avgSyllablesPerWord = totalSyllables / wordCount;
    const avgWordsPerSentence = wordCount / sentenceCount;

    let score = 206.835 - (62.3 * avgSyllablesPerWord) - avgWordsPerSentence;

    // 6. Normalizar a 0-100 (la escala original va de ~0 a ~100)
    score = Math.max(0, Math.min(100, Math.round(score)));

    return score;
};
