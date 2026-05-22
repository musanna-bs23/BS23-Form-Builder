import apiFetch from '@wordpress/api-fetch';

const namespace = '/bs23-form-builder/v1';

export function listEntries(filters = {}) {
  return apiFetch({ path: addQuery(`${namespace}/entries`, filters) });
}

export function getSummary(filters = {}) {
  return apiFetch({ path: addQuery(`${namespace}/entries/summary`, filters) });
}

export function getEntry(id) {
  return apiFetch({ path: `${namespace}/entries/${id}` });
}

export function deleteEntry(id) {
  return apiFetch({ path: `${namespace}/entries/${id}`, method: 'DELETE' });
}

export function bulkDelete(ids) {
  return apiFetch({ path: `${namespace}/entries/bulk-delete`, method: 'POST', data: { ids } });
}

export function exportEntries(filters = {}) {
  return apiFetch({ path: addQuery(`${namespace}/entries/export`, filters) });
}

function addQuery(path, filters) {
  const params = new URLSearchParams();
  Object.entries(filters).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      params.set(key, value);
    }
  });
  const query = params.toString();

  return query ? `${path}?${query}` : path;
}
