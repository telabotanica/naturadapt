import domready from "mf-js/modules/dom/ready";

domready(() => {
    const hasAdaptativeApproach = document.querySelector('#user_profile_hasAdaptativeApproach');
    const adaptativeApproachLinkRow = document.querySelector('[class="form-row form-row__field form-row__field.adaptative-approach-link-row"]');
    var radio;

    function toggleAdaptativeApproachLink() {
        radio = document.querySelector('input[name="user_profile[hasAdaptativeApproach]"]:checked');

        if (radio.value === '1') {
            adaptativeApproachLinkRow.style.display = '';
            adaptativeApproachLinkRow.querySelector('input').removeAttribute('disabled');
        } else {
            adaptativeApproachLinkRow.style.display = 'none';
            adaptativeApproachLinkRow.querySelector('input').setAttribute('disabled', 'disabled');
        }
    }
    if(hasAdaptativeApproach){
        hasAdaptativeApproach.addEventListener('change', toggleAdaptativeApproachLink);
        toggleAdaptativeApproachLink();
    }
});