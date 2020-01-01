import utilityButtonEventListener from './_utility-button-event-listener';

const init = () => {
  const $trigger = document.querySelector('.js-utility-pull-translations');
  const $progressContainer = document.querySelector('.js-utility-pull-translations-status');

  utilityButtonEventListener($trigger, $progressContainer, async (done) => {
    await fetch(`${window.Craft.baseCpUrl}/spreadsheet-translations/utilities/pull-translations`);
    done();
  });
};

export default {
  init,
};