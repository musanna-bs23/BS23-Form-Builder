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
