import pushHandles from './utility/_push-handles';
import pushLanguges from './utility/_push-languages';
import pullTranslations from './utility/_pull_translations';

const init = () => {
  pushLanguges.init();
  pushHandles.init();
  pullTranslations.init();
};

window.addEventListener('load', init);