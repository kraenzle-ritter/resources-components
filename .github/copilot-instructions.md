
# kraenzle-ritter/resources-components

The package should search for resources (links) from various selectable providers (`provider-select.blade.php`) for a Laravel model that has the trait `hasResource` (kraenzle-ritter/resources). It should also show which resources are already linked to the model (`resources-list.blade.php`).

- Laravel 11,  Livewire 3.4, Bootstrap >= 5 

All comments, internal error messages and the documentation (readme.md, changelog.md, commit messages) must be written in **English**.

```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
```

- A simple search string is used to search the providers.

## Verwendung in einer Applikation

In Anton project, for which the component is intended, the usage currently looks like this:

```
  <div class="col-md-4">
      <div class="mb-5">
          @livewire('resources-list', [$model, 'deleteButton' => true])
      </div>
      @livewire('provider-select', [$model, $providers, 'actors'])
  </div>
```
This use must continue to function.

Where $providers contains a simple list (array) that corresponds to the entries in the config(‘providers’) of the package or after vendor publish also in Anton, e.g. `[‘gnd’, ‘geonames’, ‘manual-input’]`.
