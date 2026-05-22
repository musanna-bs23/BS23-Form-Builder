import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import App from '../app';

test('renders palette groups and adds email field to canvas', async () => {
  render(<App />);

  await waitFor(() => expect(screen.getByText('Form Settings')).not.toBeNull());

  expect(screen.getByText('General Fields')).not.toBeNull();

  fireEvent.dragStart(screen.getByText('Email'), {
    dataTransfer: { setData: jest.fn() },
  });
  fireEvent.drop(screen.getByLabelText('Form canvas'), {
    dataTransfer: { getData: () => 'email' },
  });

  expect(screen.getAllByText('Email')).toHaveLength(2);
});
