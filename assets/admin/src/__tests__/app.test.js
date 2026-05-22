import { render, screen, fireEvent } from '@testing-library/react';
import App from '../app';

test('renders palette groups and adds email field to canvas', () => {
  render(<App />);

  expect(screen.getByText('General Fields')).not.toBeNull();

  fireEvent.dragStart(screen.getByText('Email'), {
    dataTransfer: { setData: jest.fn() },
  });
  fireEvent.drop(screen.getByLabelText('Form canvas'), {
    dataTransfer: { getData: () => 'email' },
  });

  expect(screen.getAllByText('Email')).toHaveLength(2);
});
