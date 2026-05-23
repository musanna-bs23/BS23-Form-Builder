import { createField, createContainer, isAllowedFieldType } from '../schema';

test('creates an email field with stable schema properties', () => {
  const field = createField('email');

  expect(field.type).toBe('email');
  expect(field.label).toBe('Email');
  expect(field.name).toBe('email');
  expect(field.required).toBe(false);
  expect(field.settings).toEqual({});
});

test('creates a four column container', () => {
  const container = createContainer(4);

  expect(container.type).toBe('container');
  expect(container.columns).toBe(4);
  expect(container.children).toEqual([[], [], [], []]);
});

test('rejects unknown field type', () => {
  expect(isAllowedFieldType('unsafe')).toBe(false);
});

test('rejects container palette item types as fields', () => {
  expect(isAllowedFieldType('container_2')).toBe(false);
});

test('rejects unsupported container column counts', () => {
  expect(() => createContainer(5)).toThrow('Invalid container column count.');
});
