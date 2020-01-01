import utilityButtonEventListener from './_utility-button-event-listener';

const init = () => {
  const $trigger = document.querySelector('.js-utility-push-languages');
  const $progressContainer = document.querySelector('.js-utility-push-languages-status');

  utilityButtonEventListener($trigger, $progressContainer, async (done) => {
    await fetch(`${window.Craft.baseCpUrl}/spreadsheet-translations/utilities/push-languages`);
    done();
  });
};

export default {
  init,
};