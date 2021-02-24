import API from './common/API.js';

// element objects:
// hyphenation input
const elHypButton = document.getElementById('hypButton');
const elHypInput = document.getElementById('hypInput');

// loading
const blockHypResultLoading = document.getElementById('hypBlockResultLoading');

// word result
const blockHypResultWord = document.getElementById('hypBlockResultWord');
const elHypResultWord = document.getElementById('hypResultWord');
const elHypResultWordCount = document.getElementById('hypResultWordCount');
const elHypResultWordList = document.getElementById('hypResultWordList');

// text result
const blockHypResultText = document.getElementById('hypBlockResultText');
const elHypResultText = document.getElementById('hypResultText');


elHypButton.onclick = function () {
    Hyphenator.handleHyphenation();
};
elHypInput.addEventListener('keyup', function (event) {
    if (event.key === 'Enter') {
        elHypButton.click();
    }
});

class Hyphenator
{
    static handleHyphenation()
    {
        let text = elHypInput.value;
        text = text.trim();
        let numberOfWords = text.split(' ').length;
        
        if (text.length < 2) {
            return;
        }

        this.showOnlyBlock(blockHypResultLoading);

        if (numberOfWords > 1) {
            this.hyphenateText(text);
        } else {
            this.hyphenateSingleWord(text);
        }
    }
    
    static hyphenateSingleWord(word)
    {
        API.Hyphenation.SingleWord(word)
            .then(response => response.json())
            .then(data => {
                elHypResultWord.innerText = data.result;

                elHypResultWordCount.innerText = data.matchedPatterns.length;

                let list = '';
                data.matchedPatterns.forEach(pattern => {
                    list += `<li>${pattern.pattern} @ ${pattern.position}</li>`;
                });

                elHypResultWordList.innerHTML = list;

                this.showOnlyBlock(blockHypResultWord);
            });
    }
    
    static hyphenateText(text)
    {
        API.Hyphenation.Text(text)
            .then(response => response.json())
            .then(data => {
                elHypResultText.innerText = data.text;

                this.showOnlyBlock(blockHypResultText);
            });
    }

    /**
     * Show only this block element and hide other 2
     * @param blockElement
     */
    static showOnlyBlock(blockElement)
    {
        blockHypResultLoading.className = 'd-none';
        blockHypResultWord.className = 'd-none';
        blockHypResultText.className = 'd-none';
        blockElement.className = '';
    }
}
