import {
  addFieldToRoot,
  addFieldToContainer,
  createContainer,
  deleteField,
  duplicateField,
  findField,
  moveField,
  updateField,
  updateFieldSettings,
} from '../schema';

test('updates root field properties and settings', () => {
  let schema = addFieldToRoot({ version: 1, fields: [] }, 'text');
  const fieldId = schema.fields[0].id;

  schema = updateField(schema, fieldId, { label: 'Full Name', name: 'full_name', required: true });
  schema = updateFieldSettings(schema, fieldId, { placeholder: 'Your name' });

  expect(findField(schema, fieldId)).toMatchObject({
    label: 'Full Name',
    name: 'full_name',
    required: true,
    settings: { placeholder: 'Your name' },
  });
});

test('duplicates deletes and reorders root fields', () => {
  let schema = addFieldToRoot({ version: 1, fields: [] }, 'text');
  schema = addFieldToRoot(schema, 'email');
  const firstId = schema.fields[0].id;

  schema = duplicateField(schema, firstId);
  expect(schema.fields).toHaveLength(3);
  expect(schema.fields[1].id).not.toBe(firstId);

  schema = moveField(schema, schema.fields[2].id, 'up');
  expect(schema.fields[1].type).toBe('email');

  schema = deleteField(schema, firstId);
  expect(findField(schema, firstId)).toBeNull();
});

test('edits field inside container column', () => {
  const container = createContainer(2);
  let schema = { version: 1, fields: [container] };
  schema = addFieldToContainer(schema, container.id, 0, 'dropdown');
  const childId = schema.fields[0].children[0][0].id;

  schema = updateFieldSettings(schema, childId, { choices: ['A', 'B'] });

  expect(findField(schema, childId).settings.choices).toEqual(['A', 'B']);
});
