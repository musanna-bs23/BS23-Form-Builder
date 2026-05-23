import { FIELD_LABELS } from './fields';

const allowedTypes = new Set(Object.keys(FIELD_LABELS).filter((type) => !type.startsWith('container_')));

export function isAllowedFieldType(type) {
  return allowedTypes.has(type);
}

export function createField(type) {
  if (!isAllowedFieldType(type)) {
    throw new Error(`Invalid field type: ${type}`);
  }

  return {
    id: `${type}_${Date.now()}_${Math.random().toString(16).slice(2)}`,
    type,
    label: FIELD_LABELS[type],
    name: type,
    required: false,
    settings: {},
  };
}

export function createContainer(columns) {
  if (![1, 2, 3, 4].includes(columns)) {
    throw new Error('Invalid container column count.');
  }

  return {
    id: `container_${Date.now()}_${Math.random().toString(16).slice(2)}`,
    type: 'container',
    columns,
    children: Array.from({ length: columns }, () => []),
  };
}

export function createPaletteItem(type) {
  if (type.startsWith('container_')) {
    return createContainer(Number(type.replace('container_', '')));
  }
  return createField(type);
}

export function addFieldToRoot(schema, type) {
  return {
    ...schema,
    fields: [...schema.fields, createPaletteItem(type)],
  };
}

export function addFieldToContainer(schema, containerId, columnIndex, type) {
  return {
    ...schema,
    fields: schema.fields.map((field) => {
      if (field.id !== containerId || field.type !== 'container') {
        return field;
      }

      const children = field.children.map((column, index) => {
        if (index !== columnIndex) {
          return column;
        }
        return [...column, createPaletteItem(type)];
      });

      return { ...field, children };
    }),
  };
}

export function findField(schema, fieldId) {
  return findInFields(schema.fields, fieldId);
}

export function updateField(schema, fieldId, updates) {
  return {
    ...schema,
    fields: updateInFields(schema.fields, fieldId, (field) => ({ ...field, ...updates })),
  };
}

export function updateFieldSettings(schema, fieldId, settings) {
  return {
    ...schema,
    fields: updateInFields(schema.fields, fieldId, (field) => ({
      ...field,
      settings: { ...(field.settings || {}), ...settings },
    })),
  };
}

export function deleteField(schema, fieldId) {
  return {
    ...schema,
    fields: deleteFromFields(schema.fields, fieldId),
  };
}

export function duplicateField(schema, fieldId) {
  return {
    ...schema,
    fields: duplicateInFields(schema.fields, fieldId),
  };
}

export function moveField(schema, fieldId, direction) {
  return {
    ...schema,
    fields: moveInFields(schema.fields, fieldId, direction),
  };
}

function findInFields(fields, fieldId) {
  for (const field of fields) {
    if (field.id === fieldId) {
      return field;
    }
    if (field.type === 'container') {
      for (const column of field.children) {
        const found = findInFields(column, fieldId);
        if (found) {
          return found;
        }
      }
    }
  }
  return null;
}

function updateInFields(fields, fieldId, updater) {
  return fields.map((field) => {
    if (field.id === fieldId) {
      return updater(field);
    }
    if (field.type !== 'container') {
      return field;
    }
    return {
      ...field,
      children: field.children.map((column) => updateInFields(column, fieldId, updater)),
    };
  });
}

function deleteFromFields(fields, fieldId) {
  return fields
    .filter((field) => field.id !== fieldId)
    .map((field) => {
      if (field.type !== 'container') {
        return field;
      }
      return {
        ...field,
        children: field.children.map((column) => deleteFromFields(column, fieldId)),
      };
    });
}

function duplicateInFields(fields, fieldId) {
  return fields.flatMap((field) => {
    if (field.id === fieldId) {
      return [field, cloneField(field)];
    }
    if (field.type !== 'container') {
      return [field];
    }
    return [{
      ...field,
      children: field.children.map((column) => duplicateInFields(column, fieldId)),
    }];
  });
}

function moveInFields(fields, fieldId, direction) {
  const index = fields.findIndex((field) => field.id === fieldId);
  if (index !== -1) {
    const target = direction === 'up' ? index - 1 : index + 1;
    if (target < 0 || target >= fields.length) {
      return fields;
    }
    const next = [...fields];
    [next[index], next[target]] = [next[target], next[index]];
    return next;
  }

  return fields.map((field) => {
    if (field.type !== 'container') {
      return field;
    }
    return {
      ...field,
      children: field.children.map((column) => moveInFields(column, fieldId, direction)),
    };
  });
}

function cloneField(field) {
  const suffix = `${Date.now()}_${Math.random().toString(16).slice(2)}`;
  if (field.type === 'container') {
    return {
      ...field,
      id: `container_${suffix}`,
      children: field.children.map((column) => column.map(cloneField)),
    };
  }
  return {
    ...field,
    id: `${field.type}_${suffix}`,
    name: `${field.name || field.type}_copy`,
  };
}
