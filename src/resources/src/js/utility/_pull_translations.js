import utilityButtonEventListener from './_utility-button-event-listener';

const init = () => {
  const $trigger = document.querySelector('.js-utility-pull-translations');
  const $progressContainer = document.querySelector('.js-utility-pull-translations-status');

  utilityButtonEventListener($trigger, $progressContainer, async (done) => {
    await fetch(`/admin/spreadsheet-translations/utilities/pull-translations`, { method: 'POST' });
    done();
  });
};

export default {
  init,
};