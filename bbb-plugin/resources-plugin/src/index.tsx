import * as React from 'react';
import * as ReactDOM from 'react-dom/client';
import ResourcesPlugin from './resources-plugin';

const pluginUuid = document.currentScript?.getAttribute('uuid') ?? 'root';
const rootElement = document.getElementById(pluginUuid);

if (rootElement) {
  ReactDOM.createRoot(rootElement).render(
    <ResourcesPlugin pluginUuid={pluginUuid} />,
  );
}
