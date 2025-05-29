import { Application } from '@hotwired/stimulus';

const app = Application.start();

// Автоподгрузка контроллеров
const context = require.context(
    'controllers',
    true,
    /\.(j|t)s$/
);

context.keys().forEach(key => {
    const module = context(key);
    const name = key.replace(/^\.\//, '')
        .replace(/\.(j|t)s$/, '')
        .replace(/\//g, '--');
    app.register(name, module.default);
});
