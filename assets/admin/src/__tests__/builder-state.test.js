import { addFieldToRoot, addFieldToContainer, createContainer } from '../schema';

test('adds field to root canvas schema', () => {
  const schema = { version: 1, fields: [] };
  const next = addFieldToRoot(schema, 'email');

  expect(next.fields).toHaveLength(1);
  expect(next.fields[0].type).toBe('email');
});

test('adds field to selected container column', () => {
  const container = createContainer(3);
  const schema = { version: 1, fields: [container] };
  const next = addFieldToContainer(schema, container.id, 1, 'text');

  expect(next.fields[0].children[1]).toHaveLength(1);
  expect(next.fields[0].children[1][0].type).toBe('text');
});

test('does not mutate schema when adding a field to root', () => {
  const schema = { version: 1, fields: [] };
  const next = addFieldToRoot(schema, 'email');

  expect(next).not.toBe(schema);
  expect(schema.fields).toEqual([]);
});

test('leaves container unchanged when column index is invalid', () => {
  const container = createContainer(2);
  const schema = { version: 1, fields: [container] };
  const next = addFieldToContainer(schema, container.id, 3, 'text');

  expect(next.fields[0].children).toEqual([[], []]);
});
