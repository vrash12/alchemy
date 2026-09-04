import {
  BbbPluginSdk,
  OptionsDropdownOption,
  pluginLogger,
} from 'bigbluebutton-html-plugin-sdk';
import * as React from 'react';
import { useEffect } from 'react';

interface ResourcesPluginProps {
  pluginUuid: string;
}

function ResourcesPlugin({ pluginUuid }: ResourcesPluginProps): React.ReactElement | null {
  BbbPluginSdk.initialize(pluginUuid);
  const pluginApi = BbbPluginSdk.getPluginApi(pluginUuid);

  useEffect(() => {
    pluginApi.setOptionsDropdownItems([
      new OptionsDropdownOption({
        label: 'Resources',
        icon: 'link',
        onClick: () => {
          window.open('https://example.com', '_blank', 'noopener,noreferrer');
          pluginLogger.info('Resources option opened');
        },
      }),
    ]);
  }, [pluginApi]);

  return null;
}

export default ResourcesPlugin;
